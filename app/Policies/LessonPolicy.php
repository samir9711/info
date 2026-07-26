<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\CourseApplication;
use App\Models\CourseInstructor;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy
{
    use HandlesAuthorization;

    /**
     * تحديد من يستطيع تشغيل فيديو الدرس.
     */
    public function streamVideo(
        User|Admin|Instructor $actor,
        Lesson $lesson
    ): bool {
        $lesson->loadMissing('course');

        $course = $lesson->course;

        if (!$course) {
            return false;
        }

        /*
         * الأدمن يستطيع مشاهدة جميع فيديوهات الدروس.
         */
        if ($actor instanceof Admin) {
            return true;
        }

        /*
         * الإنستركتور يستطيع المشاهدة إذا كان مرتبطًا بالكورس.
         */
        if ($actor instanceof Instructor) {
            $isAssignedToCourse =
                CourseInstructor::query()
                    ->where(
                        'course_id',
                        $lesson->course_id
                    )
                    ->where(
                        'instructor_id',
                        $actor->id
                    )
                    ->exists();

            /*
             * هذا الشرط اختياري:
             * اسمح أيضًا لمن أنشأ الكورس بالمشاهدة.
             */
            $isCourseCreator =
                isset($course->created_by) &&
                (int) $course->created_by ===
                (int) $actor->id;

            return $isAssignedToCourse ||
                $isCourseCreator;
        }

        /*
         * الدروس المجانية متاحة للطالب.
         */
        if (
            (bool) $course->is_free ||
            (bool) $lesson->free_preview
        ) {
            return true;
        }

        /*
         * الطالب يجب أن يكون لديه طلب كورس مقبول.
         */
        if ($actor instanceof User) {
            return CourseApplication::query()
                ->where(
                    'course_id',
                    $lesson->course_id
                )
                ->where(
                    'applicant_id',
                    $actor->id
                )
                ->where('status', 1)
                ->exists();
        }

        return false;
    }
}
