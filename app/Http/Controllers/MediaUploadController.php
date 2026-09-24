<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMediaUpload;
use App\Models\MediaUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            return response()->json([]);
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

            return response()->json([]);
        }

        if ($type === 'post-create') {

            $mediaUpload->update([
                'tus_id' => $upload['ID'] ?? null,
                'status' => 'uploading',
            ]);

            return response()->json([]);
        }

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

            ProcessMediaUpload::dispatch(
                $mediaUpload->id
            );

            return response()->json([]);
        }

        return response()->json([]);
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
}
