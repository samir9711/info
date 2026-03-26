<?php

namespace App\Http\Resources\Model;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;
use Illuminate\Database\Eloquent\Model;

class FavoriteResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data = $this->initResource(
            ModelColumnsService::getServiceFor(
                Favorite::class
            )
        );

        $data['user'] = $this->whenLoaded('user', function () {
            return $this->user ? $this->user->toArray() : null;
        });

        $data['favoritable'] = $this->whenLoaded('favoritable', function () {
            return $this->favoritable ? $this->formatFavoritable($this->favoritable) : null;
        });

        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }

    protected function formatFavoritable(Model $model): array
    {
        return [
            'type' => class_basename($model),
            'data' => $model->toArray(),
        ];
    }
}
