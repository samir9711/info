<?php

namespace App\Policies;

use App\Models\CourseApplication;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Admin;
use App\Models\Instructor;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can stream the lesson video.
     *
     * @param  \App\Models\User|\App\Models\Admin|\App\Models\Instructor  $user
     * @param  \App\Models\Lesson  $lesson
     * @return bool
     */
    public function streamVideo($user, Lesson $lesson): bool
    {
        return $this->canAccessLesson($user, $lesson);
    }

    /**
     * Determine if the user can refresh the video playback session.
     *
     * @param  \App\Models\User|\App\Models\Admin|\App\Models\Instructor  $user
     * @param  \App\Models\Lesson  $lesson
     * @param  string  $playbackSessionId
     * @return bool
     */
    public function refreshVideo($user, Lesson $lesson, string $playbackSessionId): bool
    {
        // The session ownership is checked in the service, but we can also check here if we have the session.
        // For simplicity, we defer to service, but we can return true if the user can access the lesson.
        // The actual session validation will be in the service.
        return $this->canAccessLesson($user, $lesson);
    }

    /**
     * Determine if the user can get the video file (given a valid playback session).
     *
     * @param  \App\Models\User|\App\Models\Admin|\App\Models\Instructor  $user
     * @param  \App\Models\Lesson  $lesson
     * @param  string  $playbackSessionId
     * @return bool
     */
    public function getVideoFile($user, Lesson $lesson, string $playbackSessionId): bool
    {
        // The session validation is done in the service, but we can check that the user has access to the lesson.
        return $this->canAccessLesson($user, $lesson);
    }

    /**
     * Check if the user has access to the lesson (based on the type of user).
     *
     * @param  mixed  $user
     * @param  \App\Models\Lesson  $lesson
     * @return bool
     */
    protected function canAccessLesson($user, Lesson $lesson): bool
    {
        // Load the course relationship if not loaded
        $lesson->loadMissing('course');

        // Free lessons or free preview are accessible to anyone (including guests? but we assume authenticated)
        if ($lesson->course->is_free || $lesson->free_preview) {
            return true;
        }

        // For non-free lessons, check based on user type
        if ($user instanceof User) {
            // Regular user: check if they have an approved application for the course
            return CourseApplication::where('course_id', $lesson->course_id)
                ->where('applicant_id', $user->id)
                ->where('status', 1)
                ->exists();
        }

        if ($user instanceof Admin) {
            // Admin can access any lesson
            return true;
        }

        if ($user instanceof Instructor) {
            // Instructor can access if they created the course
            return $lesson->course->created_by === $user->id;
        }

        return false;
    }
}
