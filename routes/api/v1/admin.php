<?php


use App\Http\Controllers\Article\ArticleController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\ContactUs\ContactUsController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\Lesson\LessonController;
use App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController;
use App\Http\Controllers\PrivacyUsage\PrivacyUsageController;
use App\Http\Controllers\TermsCondition\TermsConditionController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Vocabulary\VocabularyController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
    });

    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
    });


    Route::prefix('article')->middleware('auth:admin')->group(function () {
        Route::get('/all/paginated', [ArticleController::class, 'allPaginated']);
        Route::get('/all',           [ArticleController::class, 'all']);
        Route::post('/show',         [ArticleController::class, 'show']);
        Route::post('/create',       [ArticleController::class, 'store']);
        Route::post('/update',       [ArticleController::class, 'update']);
        Route::delete('/destroy',    [ArticleController::class, 'destroy']);

    });

    Route::prefix('category')->middleware('auth:admin')->group(function () {
        Route::get('/all/paginated', [CategoryController::class, 'allPaginated']);
        Route::get('/all',           [CategoryController::class, 'all']);
        Route::post('/show',         [CategoryController::class, 'show']);
        Route::post('/create',       [CategoryController::class, 'store']);
        Route::post('/update',       [CategoryController::class, 'update']);
        Route::delete('/destroy',    [CategoryController::class, 'destroy']);

    });


    Route::prefix('vocabulary')->group(function () {
        Route::get('/all/paginated', [VocabularyController::class, 'allPaginated']);
        Route::get('/all',           [VocabularyController::class, 'all']);
        Route::post('/show',         [VocabularyController::class, 'show']);
        Route::post('/create',       [VocabularyController::class, 'store']);
        Route::post('/update',       [VocabularyController::class, 'update']);
        Route::delete('/destroy',    [VocabularyController::class, 'destroy']);

    });


    Route::prefix('user')->middleware('auth:admin')->group(function () {
        Route::get('/all/paginated', [UserController::class, 'allPaginated']);
        Route::get('/all',           [UserController::class, 'all']);
        Route::post('/show',         [UserController::class, 'show']);
        Route::post('/create',       [UserController::class, 'store']);
        Route::post('/update',       [UserController::class, 'update']);
        Route::delete('/destroy',    [UserController::class, 'destroy']);
    });


    Route::prefix('terms-condition')->middleware('auth:admin')->group(function () {
        Route::post('/show',         [TermsConditionController::class, 'show']);
        Route::post('/create',       [TermsConditionController::class, 'store']);
    });



    Route::prefix('privacy-usage')->middleware('auth:admin')->group(function () {
        Route::post('/show',         [PrivacyUsageController::class, 'show']);
        Route::post('/create',       [PrivacyUsageController::class, 'store']);
    });

    Route::prefix('privacy-policy')->middleware('auth:admin')->group(function () {
        Route::post('/show',         [PrivacyPolicyController::class, 'show']);
        Route::post('/create',       [PrivacyPolicyController::class, 'store']);
    });


    Route::prefix('faq')->middleware('auth:admin')->group(function () {
        Route::get('/all/paginated', [FaqController::class, 'allPaginated']);
        Route::get('/all',           [FaqController::class, 'all']);
        Route::post('/show',         [FaqController::class, 'show']);
        Route::post('/create',       [FaqController::class, 'store']);
        Route::post('/update',       [FaqController::class, 'update']);
        Route::delete('/destroy',    [FaqController::class, 'destroy']);
    });



    Route::prefix('contact-us')->middleware('auth:admin')->group(function () {
        Route::get('/all',           [ContactUsController::class, 'all']);
        Route::post('/show',         [ContactUsController::class, 'show']);
        Route::post('/create',       [ContactUsController::class, 'store']);
    });


    Route::prefix('course')->middleware('auth:admin')->group(function () {
        Route::get('/all/paginated', [CourseController::class, 'allPaginated']);
        Route::get('/all',           [CourseController::class, 'all']);
        Route::post('/show',         [CourseController::class, 'show']);
        Route::post('/create',       [CourseController::class, 'store']);
        Route::post('/update',       [CourseController::class, 'update']);
        Route::delete('/destroy',    [CourseController::class, 'destroy']);
    });

    Route::prefix('lesson')->middleware('auth:admin')->group(function () {
        Route::get('/all/paginated', [LessonController::class, 'allPaginated']);
        Route::get('/all',           [LessonController::class, 'all']);
        Route::post('/show',         [LessonController::class, 'show']);
        Route::post('/create',       [LessonController::class, 'store']);
        Route::post('/update',       [LessonController::class, 'update']);
        Route::delete('/destroy',    [LessonController::class, 'destroy']);
    });






});

