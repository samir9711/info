<?php

namespace App\Http\Controllers;

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
                'in:podcast,event_video,single_lesson,lesson'
            ],

            'model_id' => [
                'required',
                'integer',
            ],

            'model_type' => [
                'required',
                'string',
                'in:podcast,event_video,single_lesson,lesson'
            ],

            'file_name' => [
                'required',
                'string',
                'max:255',
            ],

            'mime_type' => [
                'required',
                'string',
                'max:100',
            ],

            'size' => [
                'required',
                'integer',
                'min:1',
                'max:10737418240'
            ],
        ]);

        $user = $request->user();

        $upload = MediaUpload::create([
            'uuid' => (string) Str::uuid(),

            'user_id' => $user?->id,

            'type' => $validated['type'],

            'model_id' => $validated['model_id'],

            'model_type' => $validated['model_type'],

            'original_name' => $validated['file_name'],

            'mime_type' => $validated['mime_type'],

            'size' => $validated['size'],

            'status' => 'pending',

            'started_at' => now(),
        ]);

        return response()->json([
            'upload_id' => $upload->uuid,

            'endpoint' => url('/tus/'),

            'metadata' => [
                'upload_id' => $upload->uuid,
                'filename' => base64_encode($validated['file_name']),
                'filetype' => base64_encode($validated['mime_type']),
            ],
        ], 201);
    }


    public function tusdHook(Request $request)
    {
        $type = $request->input('Type');

        if ($type !== 'post-finish') {
            return response()->json([]);
        }

        $upload = $request->input('Event.Upload');

        if (! $upload) {
            return response()->json([]);
        }

        $metadata = $upload['MetaData'] ?? [];

        $uploadUuid = $metadata['upload_id'] ?? null;

        if (! $uploadUuid) {
            return response()->json([]);
        }

        $mediaUpload = MediaUpload::where(
            'uuid',
            $uploadUuid
        )->first();

        if (! $mediaUpload) {
            return response()->json([]);
        }

        $storage = $upload['Storage'] ?? [];

        $path = $storage['Path'] ?? null;

        $mediaUpload->update([
            'tus_id' => $upload['ID'] ?? null,

            'uploaded_size' => $upload['Offset'] ?? 0,

            'path' => $path,

            'status' => 'completed',

            'completed_at' => now(),
        ]);

        /*
        * هنا نستطيع لاحقًا تشغيل FFmpeg.
        */

        return response()->json([]);
    }
}
