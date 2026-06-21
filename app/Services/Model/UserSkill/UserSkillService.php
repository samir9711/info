<?php

namespace App\Services\Model\UserSkill;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\UserSkill;
use App\Http\Resources\Model\UserSkillResource;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserSkillService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = UserSkill::class
        );
        $this->resource = UserSkillResource::class;
    }

    protected function userId(): int
    {
        $userId = auth('user')->id();

        if (!$userId) {
            throw ValidationException::withMessages([
                'auth' => ['Only user accounts can perform this action.'],
            ]);
        }

        return (int) $userId;
    }

    public function storeMany(BasicRequest $request): mixed
    {
        $data = $request->validated();

        $skills = collect($data['skills'] ?? [])
            ->filter(fn ($item) => is_array($item) && !empty($item['skill_id']))
            ->map(fn ($item) => [
                'skill_id' => (int) $item['skill_id'],
                'note' => $item['note'] ?? null,
            ])
            ->unique('skill_id')
            ->values();

        if ($skills->isEmpty()) {
            throw ValidationException::withMessages([
                'skills' => ['At least one skill is required.'],
            ]);
        }

        $userId = $this->userId();

        return DB::transaction(function () use ($userId, $skills) {
            $skillIds = $skills->pluck('skill_id')->all();

            $existing = UserSkill::query()
                ->where('user_id', $userId)
                ->whereIn('skill_id', $skillIds)
                ->get()
                ->keyBy('skill_id');

            $createdOrUpdatedIds = [];

            foreach ($skills as $item) {
                $skillId = $item['skill_id'];

                if ($existing->has($skillId)) {
                    // إذا كانت موجودة مسبقًا، نحدّث note بدل إنشاء سجل جديد
                    $row = $existing->get($skillId);

                    $row->update([
                        'note' => $item['note'],
                    ]);

                    $createdOrUpdatedIds[] = $row->id;
                    continue;
                }

                $row = UserSkill::create([
                    'user_id' => $userId,
                    'skill_id' => $skillId,
                    'note' => $item['note'],
                ]);

                $createdOrUpdatedIds[] = $row->id;
            }

            $results = UserSkill::with('skill')
                ->whereIn('id', $createdOrUpdatedIds)
                ->orderByDesc('id')
                ->get();

            return UserSkillResource::collection($results);
        });
    }

    public function destroyMany(Request $request): mixed
    {
        $userId = $this->userId();

        $validated = validator($request->all(), [
            'ids' => 'nullable|array|min:1',
            'ids.*' => 'integer|exists:user_skills,id',
            'skill_ids' => 'nullable|array|min:1',
            'skill_ids.*' => 'integer|exists:skills,id',
        ])->validate();

        if (empty($validated['ids']) && empty($validated['skill_ids'])) {
            throw ValidationException::withMessages([
                'ids' => ['Provide ids or skill_ids to delete.'],
            ]);
        }

        return DB::transaction(function () use ($userId, $validated) {
            $query = UserSkill::query()->where('user_id', $userId);

            if (!empty($validated['ids'])) {
                $query->whereIn('id', $validated['ids']);
            }

            if (!empty($validated['skill_ids'])) {
                $query->whereIn('skill_id', $validated['skill_ids']);
            }

            $deletedCount = $query->delete();

            return [
                'deleted_count' => $deletedCount,
                'message' => $deletedCount ? 'skills deleted' : 'no skills deleted',
            ];
        });
    }

    public function getMySkills(): mixed
    {
        $userId = $this->userId();

        $results = UserSkill::with('skill')
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get();

        return UserSkillResource::collection($results);
    }
}
