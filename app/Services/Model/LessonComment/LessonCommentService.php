<?php

namespace App\Services\Model\LessonComment;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\LessonComment;
use App\Http\Resources\Model\LessonCommentResource;

class LessonCommentService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = LessonComment::class
        );

        $this->resource = LessonCommentResource::class;
    }
}