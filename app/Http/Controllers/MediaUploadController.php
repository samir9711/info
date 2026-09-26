<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMediaUpload;
use App\Models\MediaUpload;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MediaUploadController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'in:podcast',
            ],

            'model_id' => [
                'required',
                'integer',
                'exists:podcasts,id',
            ],

            'model_type' => [
                'required',
                'string',
                'in:podcast',
            ],

            'file_name' => [
                'required',
                'string',
                'max:255',
            ],

            'mime_type' => [
                'required',
                'string',
                'in:video/mp4,video/quicktime,video/webm,video/x-matroska',
            ],

            'size' => [
                'required',
                'integer',
                'min:1',
                'max:10737418240',
            ],
        ]);

        $admin = $request->user('admin');

        $plainUploadToken = Str::random(64);

        $upload = MediaUpload::create([
            'uuid' => (string) Str::uuid(),

            'admin_id' => $admin?->id,

            'type' => $validated['type'],

            'model_id' => $validated['model_id'],

            'model_type' => $validated['model_type'],

            'original_name' => $validated['file_name'],

            'mime_type' => $validated['mime_type'],

            'size' => $validated['size'],

            'uploaded_size' => 0,

            'upload_token_hash' => hash(
                'sha256',
                $plainUploadToken
            ),

            'status' => 'pending',

            'started_at' => now(),
        ]);

        return response()->json([
            'upload_id' => $upload->uuid,

            'endpoint' => rtrim(url('/tus'), '/') . '/',

            'metadata' => [
                'upload_id' => $upload->uuid,

                'upload_token' => $plainUploadToken,

                'filename' => $validated['file_name'],

                'filetype' => $validated['mime_type'],
            ],
        ], 201);
    }


    public function tusdHook(Request $request)
    {
        $type = $request->input('Type');

        $upload = $request->input('Event.Upload');

        if (! $upload) {
            return $this->emptyTusHookResponse();
        }

        $metadata = $upload['MetaData'] ?? [];

        $uploadUuid = $metadata['upload_id'] ?? null;

        $uploadToken = $metadata['upload_token'] ?? null;

        if (! $uploadUuid || ! $uploadToken) {
            return $this->rejectTusUpload(
                'Missing upload credentials.'
            );
        }

        $mediaUpload = MediaUpload::where(
            'uuid',
            $uploadUuid
        )->first();

        if (! $mediaUpload) {
            return $this->rejectTusUpload(
                'Upload session was not found.'
            );
        }

        $expectedHash = $mediaUpload->upload_token_hash;

        $receivedHash = hash(
            'sha256',
            $uploadToken
        );

        if (
            ! $expectedHash ||
            ! hash_equals(
                $expectedHash,
                $receivedHash
            )
        ) {
            return $this->rejectTusUpload(
                'Invalid upload token.'
            );
        }

        /*
        * =====================================================
        * PRE CREATE
        * =====================================================
        */
        if ($type === 'pre-create') {

            $size = (int) ($upload['Size'] ?? 0);

            if (
                $size <= 0 ||
                $size !== (int) $mediaUpload->size
            ) {
                return $this->rejectTusUpload(
                    'Invalid upload size.'
                );
            }

            if (! in_array(
                $mediaUpload->status,
                ['pending', 'uploading'],
                true
            )) {
                return $this->rejectTusUpload(
                    'Upload session is not active.'
                );
            }

            return $this->emptyTusHookResponse();
        }

        /*
        * =====================================================
        * POST CREATE
        * =====================================================
        */
        if ($type === 'post-create') {

            $updates = [
                'tus_id' => $upload['ID']
                    ?? $mediaUpload->tus_id,
            ];

            /*
            * لا نرجع completed/processing/ready إلى uploading
            * إذا وصل post-create متأخرًا.
            */
            if (in_array(
                $mediaUpload->status,
                ['pending', 'uploading'],
                true
            )) {
                $updates['status'] = 'uploading';
            }

            $mediaUpload->update($updates);

            return $this->emptyTusHookResponse();
        }

        /*
        * =====================================================
        * POST FINISH
        * =====================================================
        */
        if ($type === 'post-finish') {

            $storage = $upload['Storage'] ?? [];

            $path = $storage['Path'] ?? null;

            $mediaUpload->update([
                'tus_id' => $upload['ID']
                    ?? $mediaUpload->tus_id,

                'uploaded_size' => $upload['Offset']
                    ?? $mediaUpload->size,

                'path' => $path,

                'status' => 'completed',

                'completed_at' => now(),

                'error' => null,

                'failed_at' => null,
            ]);

            /*
            * مهم:
            * worker عندنا يستمع إلى media queue.
            */
            ProcessMediaUpload::dispatch(
                $mediaUpload->id
            )->onQueue('media');

            return $this->emptyTusHookResponse();
        }

        return $this->emptyTusHookResponse();
    }

    private function emptyTusHookResponse()
    {
        /*
        * مهم:
        * tusd يريد HookResponse كـ JSON object:
        *
        * {}
        *
        * وليس:
        *
        * []
        */
        return response()->json(
            new \stdClass()
        );
    }

    private function rejectTusUpload(string $message)
    {
        return response()->json([
            'RejectUpload' => true,

            'HTTPResponse' => [
                'StatusCode' => 403,

                'Body' => json_encode([
                    'message' => $message,
                ]),

                'Header' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ]);
    }

    public function status(
        Request $request,
        string $uuid
    ) {
        $admin =
            $request->user(
                'admin'
            );

        $upload =
            MediaUpload::where(
                'uuid',
                $uuid
            )
                ->where(
                    'admin_id',
                    $admin->id
                )
                ->firstOrFail();

        $podcast = null;

        if (
            $upload->model_type ===
                'podcast' &&
            $upload->model_id
        ) {
            $podcast =
                Podcast::find(
                    $upload->model_id
                );
        }

        $hlsMasterUrl = null;

        if (
            $podcast &&
            $podcast->hls_status ===
                'ready' &&
            $podcast->hls_path
        ) {
            $hlsDisk =
                $podcast->hls_disk
                    ?: 'public';

            $hlsMasterUrl =
                Storage::disk(
                    $hlsDisk
                )->url(
                    trim(
                        $podcast->hls_path,
                        '/'
                    ) .
                    '/master.m3u8'
                );
        }

        return response()->json([
            'upload_id' =>
                $upload->uuid,

            'status' =>
                $upload->status,

            'size' =>
                (int) $upload->size,

            'uploaded_size' =>
                (int)
                    $upload
                        ->uploaded_size,

            /*
            * MP4 النهائي.
            */
            'path' => (
                $upload->status ===
                    'ready'
            )
                ? $upload->path
                : null,

            'url' => (
                $upload->status ===
                    'ready' &&
                $upload->path
            )
                ? Storage::disk(
                    'public'
                )->url(
                    $upload->path
                )
                : null,

            /*
            * HLS.
            */
            'hls_status' =>
                $podcast?->hls_status,

            'hls_master_url' =>
                $hlsMasterUrl,

            'error' =>
                $upload->error,

            /*
            * مهم للـ Admin فقط.
            */
            'hls_error' =>
                $podcast?->hls_error,
        ]);
    }
}
