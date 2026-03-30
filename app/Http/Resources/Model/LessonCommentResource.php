<?php

namespace App\Http\Resources\Model;

use App\Models\LessonComment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class LessonCommentResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data = $this->initResource(
            ModelColumnsService::getServiceFor(LessonComment::class)
        );

        unset($data['user_id'], $data['admin_id']);

        $data['author'] = $this->user
            ? [
                'type' => 'user',
                'id'   => $this->user->id,
                'first_name' => $this->user->first_name ?? null,
                'last_name' => $this->user->last_name ?? null,
                'image' => $this->user->image ?? null,
                'email' => $this->user->email ?? null,
            ]
            : ($this->admin
                ? [
                    'type' => 'admin',
                   
                    'name' => $this->admin->name ?? null,

                ]
                : null);

        $data['parent'] = $this->whenLoaded('parent', function () {
            return $this->parent ? new self($this->parent) : null;
        });

        $data['replies'] = $this->whenLoaded('replies', function () {
            return self::collection($this->replies);
        });

        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
