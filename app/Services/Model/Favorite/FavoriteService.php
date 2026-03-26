<?php

namespace App\Services\Model\Favorite;

use App\Models\Vocabulary;
use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Favorite;
use App\Http\Resources\Model\FavoriteResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Article;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class FavoriteService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Favorite::class
        );

        $this->resource = FavoriteResource::class;
        $this->relations = ['favoritable'];
    }

    protected array $allowedTypes = [
        'course'  => Course::class,
        'lesson'  => Lesson::class,
        'article' => Article::class,
        'vocabulary' => Vocabulary::class,
    ];

    protected function resolveUserId(Request $request): int
    {
        $user = $request->user('user') ?? $request->user();

        if (! $user) {
            throw new HttpResponseException(response()->json([
                'message' => 'Unauthenticated.',
            ], 401));
        }

        return (int) $user->id;
    }

    protected function resolveTypeModel(string $type): string
    {
        $type = strtolower(trim($type));

        if (!isset($this->allowedTypes[$type])) {
            throw new HttpResponseException(response()->json([
                'message' => 'Invalid favorite type.',
            ], 422));
        }

        return $this->allowedTypes[$type];
    }

    public function toggle(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'string'],
            'favoritable_id' => ['required', 'integer'],
            'note' => ['nullable', 'string'],
        ]);

        $userId = $this->resolveUserId($request);
        $modelClass = $this->resolveTypeModel($data['type']);

        $target = $modelClass::find($data['favoritable_id']);

        if (! $target) {
            throw new HttpResponseException(response()->json([
                'message' => 'Target not found.',
            ], 404));
        }

        $favorite = Favorite::withTrashed()
            ->where('user_id', $userId)
            ->where('favoritable_type', $modelClass)
            ->where('favoritable_id', $data['favoritable_id'])
            ->first();

        if ($favorite) {
            if ($favorite->trashed()) {
                $favorite->restore();
                return [
                    'message' => 'Favorite restored.',
                    'is_favorite' => true,
                    'favorite' => $this->resource::make($favorite->load($this->relations)),
                ];
            }

            $favorite->Forcedelete();

            return [
                'message' => 'Favorite removed.',
                'is_favorite' => false,
            ];
        }

        $favorite = Favorite::create([
            'user_id' => $userId,
            'favoritable_type' => $modelClass,
            'favoritable_id' => $data['favoritable_id'],
            'note' => $data['note'] ?? null,
        ]);

        return [
            'message' => 'Favorite added.',
            'is_favorite' => true,
            'favorite' => $this->resource::make($favorite->load($this->relations)),
        ];
    }

    public function myFavorites(Request $request): mixed
    {
        $userId = $this->resolveUserId($request);

        $query = $this->model::with($this->relations)
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            $modelClass = $this->resolveTypeModel($request->input('type'));
            $query->where('favoritable_type', $modelClass);
        }

        if ($request->filled('per_page')) {
            $data = $query->paginate((int) $request->input('per_page', 10));

            return [
                'favorites' => $this->resource::collection($data),
                'current_page' => $data->currentPage(),
                'next_page' => $data->nextPageUrl(),
                'previous_page' => $data->previousPageUrl(),
                'total_pages' => $data->lastPage(),
            ];
        }

        return $this->resource::collection($query->get());
    }
}
