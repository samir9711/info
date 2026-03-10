<?php

namespace App\Services\Model\Lesson;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Lesson;
use App\Http\Resources\Model\LessonResource;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Basic\BasicRequest;

class LessonService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Lesson::class
        );

        $this->resource = LessonResource::class;
        $this->relations = ['quizzes.questions.answers','course'];
    }

    public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            // create lesson (assume $data fields match fillable)
            $lesson = $this->model::create($data);

            // process quizzes if any
            if (!empty($data['quizzes']) && is_array($data['quizzes'])) {
                $this->processQuizzesForLesson($lesson, $data['quizzes']);
            }

            return $this->resource::make($lesson->load($this->relations));
        });
    }

    public function update(BasicRequest $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {
            $lesson = $this->model::with($this->relations)->findOrFail($request->id);

            // update lesson fields (only allowed fields will be saved)
            $lesson->update($data);

            // process quizzes (sync)
            if (array_key_exists('quizzes', $data)) {
                $this->processQuizzesForLesson($lesson, (array) $data['quizzes']);
            }

            return $this->resource::make($lesson->fresh()->load($this->relations));
        });
    }

    /**
     * Process quizzes array for a given lesson.
     * - create/update quizzes
     * - create/update/delete questions & answers
     * - sync lesson_quizzes pivot with 'required' attribute (sync replaces existing)
     *
     * Expected $quizzes: array of objects:
     * [
     *   { id?: int, title?:[], description?:[], required?:bool, questions?: [...] },
     *   ...
     * ]
     */
    protected function processQuizzesForLesson(Lesson $lesson, array $quizzes): void
    {
        $syncData = []; // [ quiz_id => ['required' => bool] ]

        foreach ($quizzes as $q) {
            // ensure array
            $q = (array) $q;

            // determine quiz (existing or create)
            $quiz = null;
            if (!empty($q['id'])) {
                $quiz = Quiz::find((int) $q['id']);
            }

            if ($quiz) {
                // update quiz fields if provided
                $upd = [];
                if (array_key_exists('title', $q)) $upd['title'] = $q['title'];
                if (array_key_exists('description', $q)) $upd['description'] = $q['description'];
                if (!empty($upd)) $quiz->update($upd);
            } else {
                // create new quiz (require at least title or description)
                $payload = [];
                if (array_key_exists('title', $q)) $payload['title'] = $q['title'];
                if (array_key_exists('description', $q)) $payload['description'] = $q['description'];
                $quiz = Quiz::create($payload);
            }

            if (!$quiz) continue; // safety

            // process questions if any
            if (!empty($q['questions']) && is_array($q['questions'])) {
                $this->syncQuestionsForQuiz($quiz, (array)$q['questions']);
            }

            // collect for pivot sync
            $required = array_key_exists('required', $q) ? (bool)$q['required'] : false;
            $syncData[$quiz->id] = ['required' => $required];
        }

        // sync pivot: replace existing links with provided ones
        $lesson->quizzes()->sync($syncData);
    }

    /**
     * Sync questions for a quiz (update/create). Deletes omitted questions (force).
     *
     * Expected $questions items:
     * [ { id?:int, question?:[], image?: string|null, answers?: [...] }, ... ]
     */
    protected function syncQuestionsForQuiz(Quiz $quiz, array $questions): void
    {
        $kept = [];

        foreach (array_values($questions) as $item) {
            $item = (array) $item;

            if (!empty($item['id'])) {
                $question = $quiz->questions()->find((int)$item['id']);
                if (!$question) {
                    // skip invalid id
                    continue;
                }

                // update
                $upd = [];
                if (array_key_exists('question', $item)) $upd['question'] = $item['question'];
                if (array_key_exists('image', $item)) $upd['image'] = $item['image'];
                if (!empty($upd)) $question->update($upd);
            } else {
                // create
                $payload = [
                    'quiz_id' => $quiz->id,
                ];
                if (array_key_exists('question', $item)) $payload['question'] = $item['question'];
                if (array_key_exists('image', $item)) $payload['image'] = $item['image'];
                $question = $quiz->questions()->create($payload);
            }

            if (!$question) continue;

            // process answers (if any)
            if (!empty($item['answers']) && is_array($item['answers'])) {
                $this->syncAnswersForQuestion($question, (array)$item['answers']);
            }

            $kept[] = $question->id;
        }

        // delete questions not kept (force delete)
        $existingIds = $quiz->questions()->pluck('id')->toArray();
        $toDelete = array_diff($existingIds, $kept);
        if (!empty($toDelete)) {
            foreach ($toDelete as $id) {
                $q = Question::find($id);
                if ($q) $q->forceDelete();
            }
        }
    }

    /**
     * Sync answers for a question (update/create). Deletes omitted answers (force).
     *
     * Expected $answers: [ { id?:int, answer?:[], is_correct?:bool }, ... ]
     */
    protected function syncAnswersForQuestion(Question $question, array $answers): void
    {
        $kept = [];

        foreach ($answers as $a) {
            $a = (array) $a;

            if (!empty($a['id'])) {
                $answer = $question->answers()->find((int)$a['id']);
                if (!$answer) continue;

                $upd = [];
                if (array_key_exists('answer', $a)) $upd['answer'] = $a['answer'];
                if (array_key_exists('is_correct', $a)) $upd['is_correct'] = (bool)$a['is_correct'];
                if (!empty($upd)) $answer->update($upd);
            } else {
                $payload = [
                    'question_id' => $question->id,
                ];
                if (array_key_exists('answer', $a)) $payload['answer'] = $a['answer'];
                if (array_key_exists('is_correct', $a)) $payload['is_correct'] = (bool)$a['is_correct'];
                $answer = $question->answers()->create($payload);
            }

            if ($answer) $kept[] = $answer->id;
        }

        // delete omitted answers (force)
        $existing = $question->answers()->pluck('id')->toArray();
        $toDelete = array_diff($existing, $kept);
        if (!empty($toDelete)) {
            foreach ($toDelete as $id) {
                $ans = Answer::find($id);
                if ($ans) $ans->forceDelete();
            }
        }
    }
}
