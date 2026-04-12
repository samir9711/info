<?php

namespace App\Services\Model\CourseQuizAttempt;

use App\Http\Requests\Model\SubmitCourseQuizRequest;
use App\Models\CourseQuiz;
use App\Models\CourseQuizAttemptAnswer;
use App\Models\LessonView;
use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CourseQuizAttempt;
use App\Http\Resources\Model\CourseQuizAttemptResource;
use Illuminate\Http\Request;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Setting;
use App\Models\Certificate;
use Illuminate\Support\Str;

class CourseQuizAttemptService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CourseQuizAttempt::class
        );

        $this->resource = CourseQuizAttemptResource::class;
    }


    public function submit(SubmitCourseQuizRequest $request): mixed
    {
        $data = $request->validated();
        $userId = auth()->id();

        if (!$userId) {
            throw ValidationException::withMessages([
                'user_id' => ['Unauthenticated user.'],
            ]);
        }

        return DB::transaction(function () use ($data, $userId) {

            $courseQuiz = CourseQuiz::with([
                'course.lessons',
                'quiz.questions.answers',
            ])->findOrFail($data['course_quiz_id']);

            $lessonIds = $courseQuiz->course->lessons->pluck('id')->values()->all();

            if (empty($lessonIds)) {
                throw ValidationException::withMessages([
                    'course_quiz_id' => ['لا يمكن حل الكويز لأن الكورس لا يحتوي على دروس.'],
                ]);
            }

            $viewedLessonIds = LessonView::where('user_id', $userId)
                ->whereIn('lesson_id', $lessonIds)
                ->distinct()
                ->pluck('lesson_id')
                ->values()
                ->all();

            if (count($viewedLessonIds) !== count($lessonIds)) {
                throw ValidationException::withMessages([
                    'course_quiz_id' => ['لا يمكنك الإجابة على الكويز حتى تشاهد جميع كل دروس الكورس.'],
                ]);
            }

            $questions = $courseQuiz->quiz->questions;

            $submittedQuestionIds = collect($data['answers'])
                ->pluck('question_id')
                ->unique()
                ->values();

            $quizQuestionIds = $questions->pluck('id')->values();

            if ($submittedQuestionIds->count() !== $quizQuestionIds->count()) {
                throw ValidationException::withMessages([
                    'answers' => ['يجب الإجابة على جميع أسئلة الكويز.'],
                ]);
            }

            if ($submittedQuestionIds->diff($quizQuestionIds)->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'answers' => ['يوجد سؤال غير تابع لهذا الكويز.'],
                ]);
            }

            $attempt = CourseQuizAttempt::create([
                'user_id' => $userId,
                'course_quiz_id' => $courseQuiz->id,
                'submitted_at' => now(),
            ]);

            $score = 0;

            foreach ($data['answers'] as $row) {
                $question = $questions->firstWhere('id', $row['question_id']);

                if (!$question) {
                    throw ValidationException::withMessages([
                        'answers' => ['يوجد سؤال غير صحيح.'],
                    ]);
                }

                $selectedAnswer = $question->answers->firstWhere('id', $row['answer_id']);

                if (!$selectedAnswer) {
                    throw ValidationException::withMessages([
                        'answers' => ['الإجابة المختارة لا تنتمي إلى هذا السؤال.'],
                    ]);
                }

                $isCorrect = (bool) $selectedAnswer->is_correct;

                CourseQuizAttemptAnswer::create([
                    'course_quiz_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer_id' => $selectedAnswer->id,
                    'is_correct' => $isCorrect,
                ]);

                if ($isCorrect) {
                    $score++;
                }
            }

            $totalQuestions = $questions->count();
            $scorePercent = $totalQuestions > 0
                ? (int) round(($score / $totalQuestions) * 100)
                : 0;

            $successMark = (int) (Setting::where('key', 'success_mark')->value('value') ?? 0);
            $passed = $scorePercent >= $successMark;

            $attempt->update([
                'score' => $score,
                'passed' => $passed,
            ]);

            if ($passed) {
                $certificate = Certificate::firstOrNew([
                    'user_id' => $userId,
                    'course_id' => $courseQuiz->course_id,
                ]);

                if (!$certificate->exists) {
                    $certificate->certificate_number = $this->generateCertificateNumber(
                        $userId,
                        $courseQuiz->course_id
                    );
                }

                $certificate->meta = array_merge($certificate->meta ?? [], [
                    'course_quiz_attempt_id' => $attempt->id,
                    'course_quiz_id' => $courseQuiz->id,
                    'score' => $score,
                    'total_questions' => $totalQuestions,
                    'score_percent' => $scorePercent,
                    'passing_mark' => $successMark,
                    'passed_at' => now()->toDateTimeString(),
                ]);

                $certificate->user_id = $userId;
                $certificate->course_id = $courseQuiz->course_id;
                $certificate->score_percent = $scorePercent;
                $certificate->passing_mark = $successMark;
                $certificate->save();
            }

            return $attempt->load([
                'courseQuiz.course',
                'courseQuiz.quiz.questions.answers',
                'answers.question',
                'answers.answer',
            ]);
        });
    }

    protected function generateCertificateNumber(int $userId, int $courseId): string
    {
        return 'CERT-' . $courseId . '-' . $userId . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));
    }
}
