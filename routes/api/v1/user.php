<?php


use App\Http\Controllers\Article\ArticleController;
use App\Http\Controllers\Auth\UserAuthController;

use App\Http\Controllers\Badge\BadgeController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\ContactUs\ContactUsController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\CourseApplication\CourseApplicationController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\Favorite\FavoriteController;
use App\Http\Controllers\InstructorRating\InstructorRatingController;
use App\Http\Controllers\Lesson\LessonController;
use App\Http\Controllers\LessonComment\LessonCommentController;
use App\Http\Controllers\LessonQuiz\LessonQuizController;
use App\Http\Controllers\LessonVideoController;
use App\Http\Controllers\LessonView\LessonViewController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController;
use App\Http\Controllers\PrivacyUsage\PrivacyUsageController;
use App\Http\Controllers\QuizAttempt\QuizAttemptController;
use App\Http\Controllers\TermsCondition\TermsConditionController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Vocabulary\VocabularyController;
use Illuminate\Support\Facades\Route;


Route::prefix('user')->name('user.')->group(function () {

    Route::post('register', [UserAuthController::class, 'register'])->name('register');
    Route::post('register/resend', [UserAuthController::class, 'resendRegisterOtp'])->name('register.resend');
    Route::post('register/verify', [UserAuthController::class, 'verifyOtp'])->name('register.verify');
    // Login (email+pass | phone+pass | social_id)
    Route::post('login', [UserAuthController::class, 'login'])->name('login');

    // Social register/login (upsert)
    //Route::post('social/register', [UserAuthController::class, 'socialRegister'])->name('social.register');

    // Reset password (email OR phone)
    Route::post('password/send-otp', [UserAuthController::class, 'sendResetOtp'])->name('password.send_otp');
    Route::post('password/verify-otp', [UserAuthController::class, 'verifyOtp'])->name('password.verify_otp'); //set the same method
    Route::post('password/reset', [UserAuthController::class, 'resetPassword'])->name('password.reset');

    // Authenticated actions (Sanctum)
    Route::middleware('auth:user')->group(function () {
        //Route::post('resend-final-otp',       [UserAuthController::class, 'resendFinalOtp'])->name('resend_final_otp');
       // Route::post('request-phone-change', [UserAuthController::class, 'requestPhoneChange'])->name('phone.request_change');
       // Route::post('verify-phone-change-otp', [UserAuthController::class, 'verifyOtp'])->name('phone.verify_change'); //set the same method
       // Route::post('update-language', [UserAuthController::class, 'updateLanguage'])->name('language.update');
        Route::post('logout', [UserAuthController::class, 'logout'])->name('logout');
    });


    Route::prefix('article')->group(function () {
        Route::get('/all/paginated', [ArticleController::class, 'allPaginated']);
        Route::get('/all',           [ArticleController::class, 'all']);
        Route::post('/show',         [ArticleController::class, 'show']);


    });

    Route::prefix('category')->group(function () {
        Route::get('/all/paginated', [CategoryController::class, 'allPaginated']);
        Route::get('/all',           [CategoryController::class, 'all']);
        Route::post('/show',         [CategoryController::class, 'show']);


    });


    Route::prefix('vocabulary')->group(function () {
        Route::get('/all/paginated', [VocabularyController::class, 'allPaginated']);
        Route::get('/all',           [VocabularyController::class, 'all']);
        Route::post('/show',         [VocabularyController::class, 'show']);
    });

    Route::prefix('terms-condition')->group(function () {
        Route::post('/show',         [TermsConditionController::class, 'show']);

    });



    Route::prefix('privacy-usage')->group(function () {
        Route::post('/show',         [PrivacyUsageController::class, 'show']);

    });

    Route::prefix('privacy-policy')->group(function () {
        Route::post('/show',         [PrivacyPolicyController::class, 'show']);

    });


    Route::prefix('faq')->group(function () {
        Route::get('/all/paginated', [FaqController::class, 'allPaginated']);
        Route::get('/all',           [FaqController::class, 'all']);
        Route::post('/show',         [FaqController::class, 'show']);

    });



    Route::prefix('contact-us')->group(function () {
        Route::get('/all',           [ContactUsController::class, 'all']);
        Route::post('/show',         [ContactUsController::class, 'show']);

    });





    Route::prefix('course')->group(function () {
        Route::get('/all/paginated', [CourseController::class, 'allPaginated']);
        Route::get('/all',           [CourseController::class, 'all']);
        Route::post('/show',         [CourseController::class, 'show']);

    });

    Route::prefix('lesson')->middleware('auth:admin')->group(function () {
        Route::get('/all/paginated', [LessonController::class, 'allPaginated']);
        Route::get('/all',           [LessonController::class, 'all']);
        Route::post('/show',         [LessonController::class, 'show']);

    });



    Route::prefix('course-application')->middleware('auth:user')->group(function () {
        Route::get('/all/paginated', [CourseApplicationController::class, 'allPaginated']);
        Route::get('/all',           [CourseApplicationController::class, 'all']);
        Route::post('/show',         [CourseApplicationController::class, 'show']);
        Route::post('/create',       [CourseApplicationController::class, 'store']);

    });



    Route::middleware(['auth:user'])->group(function () {
        Route::get('lessons/{lesson}/video', [LessonVideoController::class, 'stream'])
            ->name('api.lessons.video');
        Route::post('lessons/{lesson}/video/refresh', [LessonVideoController::class, 'refresh']);
    });

    Route::get('lessons/{lesson}/video/file', [LessonVideoController::class, 'getVideoFile'])
        ->middleware('signed')
        ->name('api.lessons.video.file');



    Route::middleware('auth:user')->group(function () {
    // تسجيل مشاهدة
    Route::post('/lesson-views', [LessonViewController::class, 'record']);

    // سجل المستخدم لدرس
    Route::post('/lesson-views/show', [LessonViewController::class, 'showForUser']);

    // احصاءات لدرس (عام)
    Route::post('/lesson-views/stats', [LessonViewController::class, 'stats']);

    // تقدّم المستخدم لكورس
    Route::post('/courses/progress', [LessonViewController::class, 'userCourseProgress']);


    });


    Route::prefix('favorite')->middleware('auth:user')->group(function () {
        Route::post('/toggle', [FavoriteController::class, 'toggle']);
        Route::get('/mine', [FavoriteController::class, 'mine']);

    });

    Route::prefix('quiz-attempt')->middleware('auth:user')->group(function () {
        Route::post('/submit',       [QuizAttemptController::class, 'submit']);


    });

    Route::post('/lesson/quizzes/preview', [LessonQuizController::class, 'preview'])->middleware('auth:user');

    Route::prefix('lesson-comment')->group(function () {
        Route::get('/all',           [LessonCommentController::class, 'all']);
        Route::post('/create',       [LessonCommentController::class, 'store'])->middleware('auth:user');
        Route::post('/update',       [LessonCommentController::class, 'update'])->middleware('auth:user');

    });


    Route::prefix('instructor-rating')->group(function () {
        Route::post('/ratings', [InstructorRatingController::class, 'byInstructor']);
        Route::post('/summary', [InstructorRatingController::class, 'summary']);

        Route::post('/create',       [InstructorRatingController::class, 'store'])->middleware('auth:user');
        Route::delete('/destroy',    [InstructorRatingController::class, 'destroy'])->middleware('auth:user');

    });

});






