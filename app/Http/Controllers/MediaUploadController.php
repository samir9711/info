<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMediaUpload;
use App\Models\MediaUpload;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Lesson;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class MediaUploadController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'in:podcast,lesson',
            ],

            'model_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'model_type' => [
                'required',
                'string',
                'in:podcast,lesson',
            ],

            'file_name' => [
                'required',
                'string',
                'max:255',
            ],

            'mime_type' => [
                'required',
                'string',
            ],

            'size' => [
                'required',
                'integer',
                'min:1',

                /*
                * 10 GiB.
                */
                'max:10737418240',
            ],
        ]);

        if (
            $validated['type'] !==
            $validated['model_type']
        ) {
            throw ValidationException::withMessages([
                'model_type' => [
                    'Upload type and model type must match.',
                ],
            ]);
        }

        $actor =
            $this->authenticatedUploader(
                $request
            );

        /*
        * ================================================
        * Podcast
        * ================================================
        */
        if (
            $validated['model_type'] ===
            'podcast'
        ) {
            if (! $actor instanceof Admin) {
                abort(403);
            }

            $model = Podcast::query()
                ->findOrFail(
                    $validated['model_id']
                );

            $allowedMime = [
                'video/mp4',
                'video/quicktime',
                'video/webm',
                'video/x-matroska',
            ];
        }

        /*
        * ================================================
        * Lesson
        * ================================================
        */
        else {
            $model = Lesson::query()
                ->findOrFail(
                    $validated['model_id']
                );

            Gate::forUser($actor)
                ->authorize(
                    'manageVideo',
                    $model
                );

            $allowedMime = [
                'video/mp4',
                'video/quicktime',
                'video/x-msvideo',
                'video/x-matroska',
                'video/webm',
                'video/ogg',
            ];
        }

        if (! in_array(
            $validated['mime_type'],
            $allowedMime,
            true
        )) {
            throw ValidationException::withMessages([
                'mime_type' => [
                    'Unsupported video format.',
                ],
            ]);
        }

        /*
        * لا نسمح بعمليتي رفع فعالتين
        * لنفس Podcast/Lesson.
        */
        $activeUploadExists =
            MediaUpload::query()
                ->where(
                    'model_type',
                    $validated['model_type']
                )
                ->where(
                    'model_id',
                    $model->id
                )
                ->whereIn(
                    'status',
                    [
                        'pending',
                        'uploading',
                        'completed',
                        'processing',
                    ]
                )
                ->exists();

        if ($activeUploadExists) {
            return response()->json([
                'message' =>
                    'Another video upload is already active for this item.',
            ], 409);
        }

        $plainUploadToken =
            Str::random(64);

        $upload =
            MediaUpload::create([
                'uuid' =>
                    (string) Str::uuid(),

                /*
                * يبقى موجودًا للتوافق مع Podcast.
                * Instructor سيكون null هنا.
                */
                'admin_id' =>
                    $actor instanceof Admin
                        ? $actor->id
                        : null,

                'type' =>
                    $validated['type'],

                'model_id' =>
                    $model->id,

                'model_type' =>
                    $validated['model_type'],

                'original_name' =>
                    $validated['file_name'],

                'mime_type' =>
                    $validated['mime_type'],

                'size' =>
                    $validated['size'],

                'uploaded_size' =>
                    0,

                'upload_token_hash' =>
                    hash(
                        'sha256',
                        $plainUploadToken
                    ),

                'status' =>
                    'pending',

                'started_at' =>
                    now(),
            ]);

        return response()->json([
            'upload_id' =>
                $upload->uuid,

            'endpoint' =>
                rtrim(
                    url('/tus'),
                    '/'
                ) . '/',

            'metadata' => [
                'upload_id' =>
                    $upload->uuid,

                'upload_token' =>
                    $plainUploadToken,

                'filename' =>
                    $validated['file_name'],

                'filetype' =>
                    $validated['mime_type'],
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
        $actor =
            $this->authenticatedUploader(
                $request
            );

        $upload =
            MediaUpload::query()
                ->where(
                    'uuid',
                    $uuid
                )
                ->firstOrFail();

        /*
        * ================================================
        * Podcast
        * ================================================
        */
        if (
            $upload->model_type ===
            'podcast'
        ) {
            if (! $actor instanceof Admin) {
                abort(403);
            }

            if (
                $upload->admin_id &&
                (int) $upload->admin_id !==
                (int) $actor->id
            ) {
                abort(403);
            }

            $podcast =
                Podcast::query()
                    ->findOrFail(
                        $upload->model_id
                    );

            $hlsMasterUrl = null;

            if (
                $podcast->hls_status ===
                    'ready' &&
                $podcast->hls_path
            ) {
                $hlsMasterUrl =
                    Storage::disk(
                        $podcast->hls_disk
                            ?: 'public'
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

                'type' =>
                    'podcast',

                'status' =>
                    $upload->status,

                'size' =>
                    (int) $upload->size,

                'uploaded_size' =>
                    (int)
                        $upload->uploaded_size,

                'path' =>
                    $upload->status ===
                        'ready'
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

                'hls_status' =>
                    $podcast->hls_status,

                'hls_master_url' =>
                    $hlsMasterUrl,

                'error' =>
                    $upload->error,

                'hls_error' =>
                    $podcast->hls_error,
            ]);
        }

        /*
        * ================================================
        * Lesson
        * ================================================
        */
        if (
            $upload->model_type ===
            'lesson'
        ) {
            $lesson =
                Lesson::query()
                    ->findOrFail(
                        $upload->model_id
                    );

            Gate::forUser($actor)
                ->authorize(
                    'manageVideo',
                    $lesson
                );

            return response()->json([
                'upload_id' =>
                    $upload->uuid,

                'type' =>
                    'lesson',

                'lesson_id' =>
                    $lesson->id,

                'status' =>
                    $upload->status,

                'size' =>
                    (int) $upload->size,

                'uploaded_size' =>
                    (int)
                        $upload->uploaded_size,

                /*
                * لا نكشف private source path
                * للفرونت.
                */
                'path' => null,

                'url' => null,

                'hls_status' =>
                    $lesson->hls_status,

                'hls_processed_at' =>
                    $lesson->hls_processed_at,

                /*
                * لا يوجد public master URL للدروس.
                *
                * التشغيل يتم من خلال
                * LessonVideoController::stream().
                */
                'hls_master_url' =>
                    null,

                'error' =>
                    $upload->error,

                'hls_error' =>
                    $lesson->hls_error,
            ]);
        }

        abort(404);
    }


    private function authenticatedUploader(
        Request $request
    ): Authenticatable {
        $actor =
            $request->user();

        if (
            ! $actor instanceof Admin &&
            ! $actor instanceof Instructor
        ) {
            throw new AuthenticationException(
                'Unauthenticated.'
            );
        }

        return $actor;
    }
}
