<?php

namespace App\Services\Model\QuizAttempt;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\QuizAttempt;
use App\Http\Resources\Model\QuizAttemptResource;
use App\Http\Requests\Model\StoreQuizAttemptRequest;

use App\Models\LessonQuiz;

use App\Models\QuizAttemptAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizAttemptService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = QuizAttempt::class
        );

        $this->resource = QuizAttemptResource::class;
    }

    public function submit(StoreQuizAttemptRequest $request): QuizAttemptResource
    {
        $data = $request->validated();

        $user = $request->user();

        $attempt = DB::transaction(function () use ($user, $data) {

            $lessonQuiz = LessonQuiz::with([
                'quiz.questions.answers',
                'lesson',
            ])->findOrFail($data['lesson_quiz_id']);

            $quiz = $lessonQuiz->quiz;

            $questions = $quiz->questions->keyBy('id');

            $attempt = QuizAttempt::create([
                'user_id'        => $user->id,
                'lesson_quiz_id' => $lessonQuiz->id,
                'score'          => 0,
                'correct_count'  => 0,
                'wrong_count'    => 0,
                'started_at'     => now(),
                'submitted_at'   => now(),
            ]);

            $correctCount = 0;
            $wrongCount = 0;
            $answeredQuestionIds = [];

            foreach ($data['answers'] as $item) {
                $questionId = (int) $item['question_id'];
                $answerId   = (int) $item['answer_id'];

                if (in_array($questionId, $answeredQuestionIds, true)) {
                    throw ValidationException::withMessages([
                        'answers' => ["Question #{$questionId} was sent more than once."],
                    ]);
                }

                if (!$questions->has($questionId)) {
                    throw ValidationException::withMessages([
                        'answers' => ["Question #{$questionId} does not belong to this quiz."],
                    ]);
                }

                $question = $questions->get($questionId);

                $selectedAnswer = $question->answers->firstWhere('id', $answerId);

                if (!$selectedAnswer) {
                    throw ValidationException::withMessages([
                        'answers' => ["Answer #{$answerId} does not belong to question #{$questionId}."],
                    ]);
                }

                $isCorrect = (bool) $selectedAnswer->is_correct;

                QuizAttemptAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_id'     => $questionId,
                    'answer_id'       => $answerId,
                    'is_correct'      => $isCorrect,
                ]);

                $answeredQuestionIds[] = $questionId;

                if ($isCorrect) {
                    $correctCount++;
                } else {
                    $wrongCount++;
                }
            }

            $totalQuestions = $quiz->questions->count();

            $score = $totalQuestions > 0
                ? round(($correctCount / $totalQuestions) * 100, 2)
                : 0;

            $attempt->update([
                'score'         => $score,
                'correct_count' => $correctCount,
                'wrong_count'   => $wrongCount,
            ]);

            return $attempt;
        });

        return new QuizAttemptResource(
            $attempt->load([
                'lessonQuiz.lesson',
                'lessonQuiz.quiz.questions.answers',
                'answers.question',
                'answers.answer',
            ])
        );
    }

}
