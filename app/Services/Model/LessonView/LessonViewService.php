<?php

namespace App\Services\Model\LessonView;

use App\Models\Lesson;
use App\Models\User;
use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\LessonView;
use App\Http\Resources\Model\LessonViewResource;
use App\Models\CourseApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LessonViewService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = LessonView::class
        );

        $this->resource = LessonViewResource::class;
    }

    public function recordView($input, $user = null)
    {
        if ($input instanceof Request) {
            $data = $input->validated();
            $device = $data['device'] ?? $input->header('X-Device') ?? null;
            $ip = $data['ip'] ?? $input->ip();
            $countAsNew = $data['count_as_new_view'] ?? null;
        } else {
            $data = $input;
            $device = $data['device'] ?? null;
            $ip = $data['ip'] ?? null;
            $countAsNew = $data['count_as_new_view'] ?? null;
        }

        if (! $user && isset($data['user_id'])) {
            $user = User::find($data['user_id']);
        }

        $lessonId = (int) ($data['lesson_id'] ?? 0);
        $lesson = Lesson::findOrFail($lessonId);

        $incomingPos = isset($data['last_watched_seconds']) ? max(0, (int)$data['last_watched_seconds']) : null;
        $lessonDuration = isset($data['lesson_duration']) ? max(1, (int)$data['lesson_duration']) : null;
        $forceCompleted = isset($data['completed']) ? (bool)$data['completed'] : false;

        $userId = $user ? $user->id : null;

        $thresholdMinutes = (int) config('lessons.new_view_threshold_minutes', 30);
        $restartWindow = (int) config('lessons.new_view_restart_seconds', 10);

        return DB::transaction(function () use (
            $userId, $lessonId, $incomingPos, $lessonDuration, $device, $ip, $forceCompleted, $countAsNew, $thresholdMinutes, $restartWindow, $lesson
        ) {
            $lv = LessonView::where('lesson_id', $lessonId)
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->first();

            $now = Carbon::now();


            if (! $lv) {
                $lv = LessonView::create([
                    'user_id' => $userId,
                    'lesson_id' => $lessonId,
                    'view_count' => 1,
                    'total_watch_seconds' => $incomingPos ?? 0,
                    'last_watched_seconds' => $incomingPos ?? 0,
                    'progress_percent' => $this->calculateProgressPercent($incomingPos, $lessonDuration),
                    'is_completed' => $forceCompleted || $this->isCompletedByPercent($incomingPos, $lessonDuration),
                    'last_viewed_at' => $now,
                    'device' => $device,
                    'ip' => $ip,
                ]);

                return $this->resource::make($lv);
            }


            $prevPos = (int) ($lv->last_watched_seconds ?? 0);
            $lastViewedAt = $lv->last_viewed_at;
            $minutesSince = $lastViewedAt ? $lastViewedAt->diffInMinutes($now) : PHP_INT_MAX;

            $isNewView = false;


            if ($countAsNew === true) {
                $isNewView = true;
            }


            if (! $isNewView && ! $lastViewedAt) {
                $isNewView = true;
            }


            if (! $isNewView && $minutesSince >= $thresholdMinutes) {
                $isNewView = true;
            }


            if (! $isNewView && $incomingPos !== null && $incomingPos < max(0, $prevPos - $restartWindow)) {
                $isNewView = true;
            }


            if (! $isNewView && $lv->is_completed) {

                if ($lessonDuration && $prevPos >= $lessonDuration) {
                    $isNewView = true;
                } elseif (! $lessonDuration && $prevPos > 0) {

                    $isNewView = true;
                }
            }


            if ($isNewView) {
                $lv->view_count = ($lv->view_count ?? 0) + 1;


                $prevPosForDelta = 0;
            } else {
                $prevPosForDelta = $prevPos;
            }


            $delta = 0;
            if ($incomingPos !== null && $incomingPos > $prevPosForDelta) {
                $delta = $incomingPos - $prevPosForDelta;
                $lv->total_watch_seconds = ($lv->total_watch_seconds ?? 0) + $delta;
                $lv->last_watched_seconds = $incomingPos;
            } elseif ($incomingPos !== null) {

                $lv->last_watched_seconds = max($prevPos, $incomingPos);
            }

          
            $lv->progress_percent = $this->calculateProgressPercent($lv->last_watched_seconds, $lessonDuration);
            $lv->is_completed = $forceCompleted || $this->isCompletedByPercent($lv->progress_percent, null, true);

            $lv->last_viewed_at = $now;
            if ($device) $lv->device = $device;
            if ($ip) $lv->ip = $ip;

            $lv->save();

            return $this->resource::make($lv);
        });
    }


    protected function calculateProgressPercent($watchedSeconds, $durationSeconds): ?int
    {
        if ($durationSeconds && $durationSeconds > 0 && $watchedSeconds !== null) {
            $pct = (int) floor(($watchedSeconds / $durationSeconds) * 100);
            return min(100, max(0, $pct));
        }
        return $watchedSeconds !== null ? 0 : null;
    }


    protected function isCompletedByPercent($progressOrSeconds, $duration = null, $isPercent = false): bool
    {
        $threshold = (int) config('lessons.completion_threshold_percent', 90);

        if ($isPercent) {
            $percent = (int) ($progressOrSeconds ?? 0);
        } else {
            if (! $duration || $duration <= 0 || $progressOrSeconds === null) return false;
            $percent = (int) floor(($progressOrSeconds / $duration) * 100);
        }

        return $percent >= $threshold;
    }

    /**
     * استرجاع سجل مشاهدة لمستخدم ودرس
     */
    public function getForUserLesson($userId, $lessonId)
    {
        return LessonView::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();
    }

    /**
     * احصاءات لدرس (اجمالي مشاهدات، مجموع الثواني، متوسط التقدم)
     */
    public function lessonStats(int $lessonId): array
    {
        $q = LessonView::where('lesson_id', $lessonId);

        $totalViews = (int) $q->count();
        $sumSeconds = (int) $q->sum('total_watch_seconds');
        $avgProgress = (int) round($q->avg('progress_percent') ?? 0);

        return [
            'lesson_id' => $lessonId,
            'total_views' => $totalViews,
            'sum_watch_seconds' => $sumSeconds,
            'avg_progress_percent' => $avgProgress,
        ];
    }

    /**
     * تقدّم المستخدم لكورس كامل (٪) = متوسط progress على الدروس المنشورة في الكورس
     */
    public function userCourseProgress(int $userId, int $courseId): array
    {
        $lessonIds = Lesson::where('course_id', $courseId)->pluck('id')->toArray();
        if (empty($lessonIds)) {
            return ['course_id' => $courseId, 'progress_percent' => 0, 'lessons_count' => 0];
        }

        $q = LessonView::where('user_id', $userId)->whereIn('lesson_id', $lessonIds);

        $countLessons = count($lessonIds);
        $sumPercent = (float) $q->sum('progress_percent');
        $lessonsWithViews = (int) $q->count();


        $avg = $countLessons > 0 ? round($sumPercent / $countLessons) : 0;

        return [
            'course_id' => $courseId,
            'progress_percent' => (int)$avg,
            'lessons_count' => $countLessons,
            'lessons_with_views' => $lessonsWithViews,
        ];
    }

    /**
     * إعادة ضبط سجل المشاهدة (اختياري، للادمن)
     */
    public function resetView(int $userId, int $lessonId): bool
    {
        $lv = LessonView::where('user_id', $userId)->where('lesson_id', $lessonId)->first();
        if (! $lv) return false;
        $lv->update([
            'view_count' => 0,
            'total_watch_seconds' => 0,
            'last_watched_seconds' => 0,
            'progress_percent' => 0,
            'is_completed' => false,
            'last_viewed_at' => null,
        ]);
        return true;
    }
}
