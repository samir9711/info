<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



/*
// Answer PUBLIC ROUTES
Route::prefix('answer')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Answer\AnswerController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Answer\AnswerController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Answer\AnswerController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Answer\AnswerController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Answer\AnswerController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Answer\AnswerController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Answer\AnswerController::class, 'deactivate']);
});

// Article PUBLIC ROUTES  (done)
Route::prefix('article')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Article\ArticleController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Article\ArticleController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Article\ArticleController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Article\ArticleController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Article\ArticleController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Article\ArticleController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Article\ArticleController::class, 'deactivate']);
});

// ArticleSection PUBLIC ROUTES  (done)
Route::prefix('article-section')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\ArticleSection\ArticleSectionController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\ArticleSection\ArticleSectionController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\ArticleSection\ArticleSectionController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\ArticleSection\ArticleSectionController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\ArticleSection\ArticleSectionController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\ArticleSection\ArticleSectionController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\ArticleSection\ArticleSectionController::class, 'deactivate']);
});

// Category PUBLIC ROUTES  (done)
Route::prefix('category')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Category\CategoryController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Category\CategoryController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Category\CategoryController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Category\CategoryController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Category\CategoryController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Category\CategoryController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Category\CategoryController::class, 'deactivate']);
});

// Certificate PUBLIC ROUTES
Route::prefix('certificate')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Certificate\CertificateController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Certificate\CertificateController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Certificate\CertificateController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Certificate\CertificateController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Certificate\CertificateController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Certificate\CertificateController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Certificate\CertificateController::class, 'deactivate']);
});

// ContactUs PUBLIC ROUTES (done)
Route::prefix('contact-us')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\ContactUs\ContactUsController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\ContactUs\ContactUsController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\ContactUs\ContactUsController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\ContactUs\ContactUsController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\ContactUs\ContactUsController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\ContactUs\ContactUsController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\ContactUs\ContactUsController::class, 'deactivate']);
});

// Course PUBLIC ROUTES  (done)
Route::prefix('course')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Course\CourseController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Course\CourseController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Course\CourseController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Course\CourseController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Course\CourseController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Course\CourseController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Course\CourseController::class, 'deactivate']);
});

// CourseApplication PUBLIC ROUTES
Route::prefix('course-application')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\CourseApplication\CourseApplicationController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\CourseApplication\CourseApplicationController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\CourseApplication\CourseApplicationController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\CourseApplication\CourseApplicationController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\CourseApplication\CourseApplicationController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\CourseApplication\CourseApplicationController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\CourseApplication\CourseApplicationController::class, 'deactivate']);
});

// CourseQuiz PUBLIC ROUTES
Route::prefix('course-quiz')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\CourseQuiz\CourseQuizController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\CourseQuiz\CourseQuizController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\CourseQuiz\CourseQuizController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\CourseQuiz\CourseQuizController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\CourseQuiz\CourseQuizController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\CourseQuiz\CourseQuizController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\CourseQuiz\CourseQuizController::class, 'deactivate']);
});

// Currency PUBLIC ROUTES


// Faq PUBLIC ROUTES   (done)
Route::prefix('faq')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Faq\FaqController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Faq\FaqController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Faq\FaqController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Faq\FaqController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Faq\FaqController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Faq\FaqController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Faq\FaqController::class, 'deactivate']);
});

// Favorite PUBLIC ROUTES
Route::prefix('favorite')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Favorite\FavoriteController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Favorite\FavoriteController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Favorite\FavoriteController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Favorite\FavoriteController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Favorite\FavoriteController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Favorite\FavoriteController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Favorite\FavoriteController::class, 'deactivate']);
});

// Lesson PUBLIC ROUTES
Route::prefix('lesson')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Lesson\LessonController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Lesson\LessonController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Lesson\LessonController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Lesson\LessonController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Lesson\LessonController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Lesson\LessonController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Lesson\LessonController::class, 'deactivate']);
});

// LessonQuiz PUBLIC ROUTES
Route::prefix('lesson-quiz')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\LessonQuiz\LessonQuizController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\LessonQuiz\LessonQuizController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\LessonQuiz\LessonQuizController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\LessonQuiz\LessonQuizController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\LessonQuiz\LessonQuizController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\LessonQuiz\LessonQuizController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\LessonQuiz\LessonQuizController::class, 'deactivate']);
});

// LessonView PUBLIC ROUTES
Route::prefix('lesson-view')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\LessonView\LessonViewController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\LessonView\LessonViewController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\LessonView\LessonViewController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\LessonView\LessonViewController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\LessonView\LessonViewController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\LessonView\LessonViewController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\LessonView\LessonViewController::class, 'deactivate']);
});

// Otp PUBLIC ROUTES
Route::prefix('otp')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Otp\OtpController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Otp\OtpController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Otp\OtpController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Otp\OtpController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Otp\OtpController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Otp\OtpController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Otp\OtpController::class, 'deactivate']);
});

// PrivacyPolicy PUBLIC ROUTES  (done)
Route::prefix('privacy-policy')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController::class, 'deactivate']);
});

// PrivacyUsage PUBLIC ROUTES  (done)
Route::prefix('privacy-usage')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\PrivacyUsage\PrivacyUsageController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\PrivacyUsage\PrivacyUsageController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\PrivacyUsage\PrivacyUsageController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\PrivacyUsage\PrivacyUsageController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\PrivacyUsage\PrivacyUsageController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\PrivacyUsage\PrivacyUsageController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\PrivacyUsage\PrivacyUsageController::class, 'deactivate']);
});

// Question PUBLIC ROUTES
Route::prefix('question')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Question\QuestionController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Question\QuestionController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Question\QuestionController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Question\QuestionController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Question\QuestionController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Question\QuestionController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Question\QuestionController::class, 'deactivate']);
});

// Quiz PUBLIC ROUTES
Route::prefix('quiz')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Quiz\QuizController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Quiz\QuizController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Quiz\QuizController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Quiz\QuizController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Quiz\QuizController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Quiz\QuizController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Quiz\QuizController::class, 'deactivate']);
});



// Setting PUBLIC ROUTES
Route::prefix('setting')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Setting\SettingController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Setting\SettingController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Setting\SettingController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Setting\SettingController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Setting\SettingController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Setting\SettingController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Setting\SettingController::class, 'deactivate']);
});

// Tag PUBLIC ROUTES
Route::prefix('tag')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Tag\TagController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Tag\TagController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Tag\TagController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Tag\TagController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Tag\TagController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Tag\TagController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Tag\TagController::class, 'deactivate']);
});

// Taggable PUBLIC ROUTES
Route::prefix('taggable')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Taggable\TaggableController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Taggable\TaggableController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Taggable\TaggableController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Taggable\TaggableController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Taggable\TaggableController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Taggable\TaggableController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Taggable\TaggableController::class, 'deactivate']);
});

// TermsCondition PUBLIC ROUTES (done)
Route::prefix('terms-condition')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\TermsCondition\TermsConditionController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\TermsCondition\TermsConditionController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\TermsCondition\TermsConditionController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\TermsCondition\TermsConditionController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\TermsCondition\TermsConditionController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\TermsCondition\TermsConditionController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\TermsCondition\TermsConditionController::class, 'deactivate']);
});

// User PUBLIC ROUTES  (done)
Route::prefix('user')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\User\UserController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\User\UserController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\User\UserController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\User\UserController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\User\UserController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\User\UserController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\User\UserController::class, 'deactivate']);
});

// Vocabulary PUBLIC ROUTES  (done)
Route::prefix('vocabulary')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Vocabulary\VocabularyController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Vocabulary\VocabularyController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Vocabulary\VocabularyController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Vocabulary\VocabularyController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Vocabulary\VocabularyController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Vocabulary\VocabularyController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Vocabulary\VocabularyController::class, 'deactivate']);
});
// QuizAttempt PUBLIC ROUTES
Route::prefix('quiz-attempt')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\QuizAttempt\QuizAttemptController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\QuizAttempt\QuizAttemptController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\QuizAttempt\QuizAttemptController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\QuizAttempt\QuizAttemptController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\QuizAttempt\QuizAttemptController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\QuizAttempt\QuizAttemptController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\QuizAttempt\QuizAttemptController::class, 'deactivate']);
});

// QuizAttemptAnswer PUBLIC ROUTES
Route::prefix('quiz-attempt-answer')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\QuizAttemptAnswer\QuizAttemptAnswerController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\QuizAttemptAnswer\QuizAttemptAnswerController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\QuizAttemptAnswer\QuizAttemptAnswerController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\QuizAttemptAnswer\QuizAttemptAnswerController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\QuizAttemptAnswer\QuizAttemptAnswerController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\QuizAttemptAnswer\QuizAttemptAnswerController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\QuizAttemptAnswer\QuizAttemptAnswerController::class, 'deactivate']);
});

// LessonComment PUBLIC ROUTES
Route::prefix('lesson-comment')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\LessonComment\LessonCommentController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\LessonComment\LessonCommentController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\LessonComment\LessonCommentController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\LessonComment\LessonCommentController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\LessonComment\LessonCommentController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\LessonComment\LessonCommentController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\LessonComment\LessonCommentController::class, 'deactivate']);
});



// Instructor PUBLIC ROUTES
Route::prefix('instructor')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\Instructor\InstructorController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\Instructor\InstructorController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\Instructor\InstructorController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\Instructor\InstructorController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\Instructor\InstructorController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\Instructor\InstructorController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\Instructor\InstructorController::class, 'deactivate']);
});

// CourseInstructor PUBLIC ROUTES
Route::prefix('course-instructor')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\CourseInstructor\CourseInstructorController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\CourseInstructor\CourseInstructorController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\CourseInstructor\CourseInstructorController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\CourseInstructor\CourseInstructorController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\CourseInstructor\CourseInstructorController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\CourseInstructor\CourseInstructorController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\CourseInstructor\CourseInstructorController::class, 'deactivate']);
});


// InstructorRating PUBLIC ROUTES
Route::prefix('instructor-rating')->group(function () {
    Route::get('/all/paginated', [\App\Http\Controllers\InstructorRating\InstructorRatingController::class, 'allPaginated']);
    Route::get('/all',           [\App\Http\Controllers\InstructorRating\InstructorRatingController::class, 'all']);
    Route::post('/show',         [\App\Http\Controllers\InstructorRating\InstructorRatingController::class, 'show']);
    Route::post('/create',       [\App\Http\Controllers\InstructorRating\InstructorRatingController::class, 'store']);
    Route::post('/update',       [\App\Http\Controllers\InstructorRating\InstructorRatingController::class, 'update']);
    Route::post('/activate',     [\App\Http\Controllers\InstructorRating\InstructorRatingController::class, 'activate']);
    Route::post('/deactivate',   [\App\Http\Controllers\InstructorRating\InstructorRatingController::class, 'deactivate']);
});
*/
