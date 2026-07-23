<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\CourseApplication;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy
{
    use HandlesAuthorization;

    public function streamVideo(
        User|Admin|Instructor $user,
        Lesson $lesson
    ): bool {
        $lesson->loadMissing('course');

        $course = $lesson->course;

        if (!$course) {
            return false;
        }

        if (
            (bool) $course->is_free ||
            (bool) $lesson->free_preview
        ) {
            return true;
        }

        if ($user instanceof Admin) {
            return true;
        }

        if ($user instanceof Instructor) {
            return (int) $course->created_by
                ===
                (int) $user->id;
        }

        if ($user instanceof User) {
            return CourseApplication::query()
                ->where(
                    'course_id',
                    $lesson->course_id
                )
                ->where(
                    'applicant_id',
                    $user->id
                )
                ->where('status', 1)
                ->exists();
        }

        return false;
    }
}
