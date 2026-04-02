<?php

namespace App\Services\Model\LessonComment;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\LessonComment;
use App\Http\Resources\Model\LessonCommentResource;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

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

    public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();

        $user = $request->user();
        $admin = $request->user('admin');

        if (!$user && !$admin) {
            abort(401);
        }

        return DB::transaction(function () use ($data, $user, $admin) {
            if (!empty($data['parent_id'])) {
                $parent = LessonComment::findOrFail($data['parent_id']);

                if ((int) $parent->lesson_id !== (int) $data['lesson_id']) {
                    throw ValidationException::withMessages([
                        'parent_id' => ['Parent comment must belong to the same lesson.'],
                    ]);
                }
            }

            $comment = LessonComment::create([
                'lesson_id' => (int) $data['lesson_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'user_id'   => $user ? $user->id : null,
                'admin_id'  => $admin ? $admin->id : null,
                'comment'   => $data['comment'],
            ]);

            $comment->load(['user', 'admin', 'lesson', 'parent']);

            return $this->resource::make($comment);
        });
    }


    public function update(BasicRequest $request): mixed
    {
        $data = $request->validated();

        $user = $request->user();
        $admin = $request->user('admin');

        if (!$user && !$admin) {
            abort(401);
        }

        $comment = LessonComment::findOrFail($request->id);

        $isOwner = $user && (int) $comment->user_id === (int) $user->id;
        $isAdmin = (bool) $admin;

        if (!$isOwner && !$isAdmin) {
            abort(403, 'You are not allowed to edit this comment.');
        }

        $comment->update([
            'comment' => $data['comment'] ?? $comment->comment,
        ]);

        $comment->load(['user', 'admin', 'lesson', 'parent']);

        return $this->resource::make($comment);
    }

    public function delete(Request $request): bool
    {
        $user = $request->user();
        $admin = $request->user('admin');

        if (!$user && !$admin) {
            abort(401);
        }

        $comment = LessonComment::findOrFail($request->id);

        $isOwner = $user && (int) $comment->user_id === (int) $user->id;
        $isAdmin = (bool) $admin;

        if (!$isOwner && !$isAdmin) {
            abort(403, 'You are not allowed to delete this comment.');
        }

        return (bool) $comment->delete();
    }

}
