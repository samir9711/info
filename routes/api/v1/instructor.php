<?php


use App\Http\Controllers\Article\ArticleController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\ContactUs\ContactUsController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\CourseApplication\CourseApplicationController;
use App\Http\Controllers\CourseCondition\CourseConditionController;
use App\Http\Controllers\CourseInstructor\CourseInstructorController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\Instructor\InstructorController;
use App\Http\Controllers\Lesson\LessonController;
use App\Http\Controllers\LessonComment\LessonCommentController;
use App\Http\Controllers\LessonVideoController;
use App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController;
use App\Http\Controllers\PrivacyUsage\PrivacyUsageController;
use App\Http\Controllers\TermsCondition\TermsConditionController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Vocabulary\VocabularyController;
use Illuminate\Support\Facades\Route;

Route::prefix('instructor')->group(function () {

    Route::prefix('course')->middleware('auth:instructor')->group(function () {

        Route::post('/show',         [CourseController::class, 'show']);
        Route::post('/create',       [CourseController::class, 'store']);
        Route::post('/update',       [CourseController::class, 'update']);

        Route::get('/my', [CourseController::class, 'myInstructorCourses']);

    });


    Route::prefix('lesson')->middleware('auth:instructor')->group(function () {
        Route::get('/all/paginated', [LessonController::class, 'allPaginated']);
        Route::get('/all',           [LessonController::class, 'all']);
        Route::post('/show',         [LessonController::class, 'show']);
        Route::post('/create',       [LessonController::class, 'store']);
        Route::post('/update',       [LessonController::class, 'update']);
        Route::delete('/destroy',    [LessonController::class, 'destroy']);
        Route::get('/{lesson}/video',[LessonVideoController::class, 'showForInstructor']);

    });



});

