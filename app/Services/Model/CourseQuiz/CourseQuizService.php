<?php

namespace App\Services\Model\CourseQuiz;

use App\Http\Resources\Model\PublicCourseQuizResource;
use App\Models\LessonView;
use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\CourseQuiz;
use App\Http\Resources\Model\CourseQuizResource;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseQuizService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = CourseQuiz::class
        );

        $this->resource = CourseQuizResource::class;
    }

    public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {

            $courseId = $data['course_id'];
            $isFinal  = (bool) ($data['is_final'] ?? false);
            $quizData = $data['quiz'];


            if ($isFinal) {
                $exists = CourseQuiz::where('course_id', $courseId)
                    ->where('is_final', true)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'is_final' => ['يوجد كويز نهائي بالفعل لهذا الكورس.'],
                    ]);
                }
            }

            // 1) إنشاء quiz
            $quiz = Quiz::create([
                'title' => $quizData['title'],
                'description' => $quizData['description'] ?? null,
            ]);

            // 2) إنشاء CourseQuiz
            $courseQuiz = CourseQuiz::create([
                'course_id' => $courseId,
                'quiz_id'   => $quiz->id,
                'is_final'  => $isFinal,
            ]);

            // 3) إنشاء الأسئلة والإجابات
            foreach ($quizData['questions'] as $questionData) {
                $question = Question::create([
                    'quiz_id'  => $quiz->id,
                    'question' => $questionData['question'],
                    'image'    => $questionData['image'] ?? null,
                ]);

                foreach ($questionData['answers'] as $answerData) {
                    Answer::create([
                        'question_id' => $question->id,
                        'answer'      => $answerData['answer'],
                        'is_correct'  => (bool) $answerData['is_correct'],
                    ]);
                }
            }

            // 4) إعادة تحميل العلاقات لعرض كامل
            $courseQuiz->load([
                'course',
                'quiz.questions.answers',
            ]);

            return $this->resource::make($courseQuiz);
        });
    }


    public function update(BasicRequest $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $courseQuiz = CourseQuiz::with(['course', 'quiz.questions.answers'])
                ->findOrFail($data['id']);

            if (isset($data['course_id'])) {
                $courseQuiz->course_id = $data['course_id'];
            }

            if (array_key_exists('is_final', $data)) {
                $isFinal = (bool) $data['is_final'];

                if ($isFinal) {
                    $exists = CourseQuiz::where('course_id', $courseQuiz->course_id)
                        ->where('is_final', true)
                        ->where('id', '!=', $courseQuiz->id)
                        ->exists();

                    if ($exists) {
                        throw ValidationException::withMessages([
                            'is_final' => ['يوجد كويز نهائي بالفعل لهذا الكورس.'],
                        ]);
                    }
                }

                $courseQuiz->is_final = $isFinal;
            }

            $courseQuiz->save();

            if (isset($data['quiz'])) {
                $this->replaceQuizTree($courseQuiz->quiz, $data['quiz']);
            }

            $courseQuiz->load(['course', 'quiz.questions.answers']);

            return $this->resource::make($courseQuiz);
        });
    }

    public function deleteOverview(Request $request): array
    {
        return DB::transaction(function () use ($request) {
            $courseQuiz = CourseQuiz::with([
                'course',
                'quiz.questions.answers',
                'quiz.lessonQuizzes',
                'quiz.courseQuizzes',
            ])->findOrFail($request->id);

            $overview = (new CourseQuizResource($courseQuiz))->resolve();

            $quiz = $courseQuiz->quiz;

            $courseQuiz->forceDelete();

            if ($quiz) {
                $stillUsed = $quiz->courseQuizzes()->exists() || $quiz->lessonQuizzes()->exists();

                if (!$stillUsed) {
                    foreach ($quiz->questions as $question) {
                        $question->answers()->forceDelete();
                        $question->forceDelete();
                    }

                    $quiz->forceDelete();
                }
            }

            return $overview;
        });
    }

    protected function replaceQuizTree(Quiz $quiz, array $quizData): void
    {
        $quiz->update([
            'title' => $quizData['title'] ?? $quiz->title,
            'description' => $quizData['description'] ?? $quiz->description,
        ]);

        $quiz->loadMissing('questions.answers');

        foreach ($quiz->questions as $question) {
            $question->answers()->forceDelete();
            $question->forceDelete();
        }

        foreach (($quizData['questions'] ?? []) as $questionData) {
            $question = $quiz->questions()->create([
                'question' => $questionData['question'],
                'image' => $questionData['image'] ?? null,
            ]);

            foreach (($questionData['answers'] ?? []) as $answerData) {
                $question->answers()->create([
                    'answer' => $answerData['answer'],
                    'is_correct' => (bool) ($answerData['is_correct'] ?? false),
                ]);
            }
        }
    }

    public function byCourseId(Request $request): mixed
    {
        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        $userId = auth()->id();

        if (!$userId) {
            throw ValidationException::withMessages([
                'user_id' => ['Unauthenticated user.'],
            ]);
        }

        $courseQuiz = CourseQuiz::with([
            'course.lessons',
            'quiz.questions.answers' => function ($q) {
                $q->select('id', 'question_id', 'answer', 'is_correct');
            },
            'quiz.questions' => function ($q) {
                $q->select('id', 'quiz_id', 'question', 'image');
            },
            'quiz' => function ($q) {
                $q->select('id', 'title', 'description');
            },
        ])
            ->where('course_id', $data['course_id'])
            ->firstOrFail();

        $lessonIds = $courseQuiz->course->lessons->pluck('id')->values()->all();

        if (empty($lessonIds)) {
            throw ValidationException::withMessages([
                'course_id' => ['لا يمكن جلب الكويز لأن الكورس لا يحتوي على دروس.'],
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
                'course_id' => ['لا يمكنك جلب الكويز حتى تشاهد جميع دروس الكورس.'],
            ]);
        }

        return new PublicCourseQuizResource($courseQuiz);
    }

    protected function generateCertificateNumber(int $userId, int $courseId): string
    {
        return 'CERT-' . $courseId . '-' . $userId . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));
    }
}
