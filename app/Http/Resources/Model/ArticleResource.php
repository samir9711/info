<?php

namespace App\Http\Resources\Model;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class ArticleResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data= $this->initResource(
            ModelColumnsService::getServiceFor(
                Article::class
            )
        );
        $data['category'] = $this->whenLoaded('category', function () {
            return $this->category ? $this->category->toArray() : null;
        });

        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);


        $sections = [];
        if ($this->resource && method_exists($this->resource, 'relationLoaded')) {
            if ($this->resource->relationLoaded('sections')) {
                $sections = $this->resource->sections;
            } else {

                $sections = $this->resource->sections()->get();
            }
        }

        $this->result['sections'] = ArticleSectionResource::collection($sections);

        return $this->result;
    }
}
