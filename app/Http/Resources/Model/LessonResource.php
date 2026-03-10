<?php

namespace App\Http\Resources\Model;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Basic\BasicResource;
use App\Services\Basic\ModelColumnsService;

class LessonResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        $data= $this->initResource(
            ModelColumnsService::getServiceFor(
                Lesson::class
            )
        );
        $data['course'] = $this->whenLoaded('course', function () {
            return $this->course ? $this->course->toArray() : null;
        });
        $action = null;
        if ($route = $request->route()) {

            $action = $route->getActionMethod() ?? null;
        }

        // عرض quizzes فقط عند create (store), update, show
        $allowed = ['store', 'update', 'show'];
        if (in_array($action, $allowed, true)) {
            $data['quizzes'] = $this->whenLoaded('quizzes', function () {
             
                return $this->quizzes->map(function ($quiz) {
                    return [
                        'id' => $quiz->id,
                        'title' => $quiz->title,
                        'description' => $quiz->description,
                        'questions' => $quiz->questions->map(function ($q) {
                            return [
                                'id' => $q->id,
                                'question' => $q->question,
                                'image' => $q->image,
                                'answers' => $q->answers->map(function ($a) {
                                    return [
                                        'id' => $a->id,
                                        'answer' => $a->answer,
                                        'is_correct' => (bool) $a->is_correct,
                                    ];
                                })->toArray(),
                            ];
                        })->toArray(),
                    ];
                })->toArray();
            });
        }

        return $data;
    }

    protected function initResource($modelColumnsService): array
    {
        $this->result = parent::initResource($modelColumnsService);

        return array_merge($this->result, []);
    }
}
