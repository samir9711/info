<?php

namespace App\Http\Resources\Model;

use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;
use Illuminate\Support\Facades\Storage;

class PodcastResource extends BasicResource
{
    public function toArray(
        Request $request
    ): array {
        $data =
            $this->initResource(
                ModelColumnsService::getServiceFor(
                    Podcast::class
                )
            );

        $data['collection'] =
            $this->whenLoaded(
                'collection',
                function () {
                    return $this->collection
                        ? $this->collection
                            ->toArray()
                        : null;
                }
            );

        $data['hls_master_url'] = (
            $this->hls_status ===
                'ready' &&
            $this->hls_path
        )
            ? Storage::disk(
                $this->hls_disk
                    ?: 'public'
            )->url(
                trim(
                    $this->hls_path,
                    '/'
                ) .
                '/master.m3u8'
            )
            : null;

        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
