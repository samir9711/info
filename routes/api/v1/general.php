<?php

use App\Http\Controllers\AboutUs\AboutUsController;
use App\Http\Controllers\Auth\PublicAuthController;
use App\Http\Controllers\Auth\UnifiedAuthController;
use App\Http\Controllers\Currency\CurrencyController;
use App\Http\Controllers\Directorate\DirectorateController;
use App\Http\Controllers\Establishment\EstablishmentController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\EventVideo\EventVideoController;
use App\Http\Controllers\Ministry\MinistryController;
use App\Http\Controllers\MinistryAccount\MinistryAccountController;
use App\Http\Controllers\Podcast\PodcastController;
use App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController;
use App\Http\Controllers\SingleLesson\SingleLessonController;
use App\Http\Controllers\TermsCondition\TermsConditionController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;


    Route::post('upload/{folder}/single', [UploadController::class, 'single'])
        ->where('folder', '[A-Za-z0-9_-]+');

    Route::post('upload/{folder}/multiple', [UploadController::class, 'multiple'])
        ->where('folder', '[A-Za-z0-9_-]+');

    Route::post('upload/video/{folder}', [UploadController::class, 'singleVideo']);

    Route::post('lessons/{lesson}/video',[UploadController::class, 'uploadVideo'])
        ->whereNumber('lesson')
        ->name('lessons.video.upload');

    Route::prefix('currency')->group(function () {
        Route::get('/all/paginated', [CurrencyController::class, 'allPaginated']);
        Route::get('/all',           [CurrencyController::class, 'all']);
        Route::post('/show',         [CurrencyController::class, 'show']);
    });



    Route::post('login', [PublicAuthController::class, 'login']);


    Route::prefix('user')->group(function () {
        Route::get('/all/paginated', [UserController::class, 'allPaginated']);
        Route::get('/all',           [UserController::class, 'all']);
        Route::post('/show',         [UserController::class, 'show']);

    });

    Route::prefix('podcast')->group(function () {
        Route::get('/all/paginated', [PodcastController::class, 'allPaginated']);
        Route::get('/all',           [PodcastController::class, 'all']);
        Route::post('/show',         [PodcastController::class, 'show']);

    });


    Route::prefix('single-lesson')->group(function () {
        Route::get('/all/paginated', [SingleLessonController::class, 'allPaginated']);
        Route::get('/all',           [SingleLessonController::class, 'all']);
        Route::post('/show',         [SingleLessonController::class, 'show']);

    });

    Route::prefix('event')->group(function () {
        Route::get('/all/paginated', [EventController::class, 'allPaginated']);
        Route::get('/all',           [EventController::class, 'all']);
        Route::post('/show',         [EventController::class, 'show']);

    });

    Route::prefix('event-video')->group(function () {
        Route::get('/all/paginated', [EventVideoController::class, 'allPaginated']);
        Route::get('/all',           [EventVideoController::class, 'all']);
        Route::post('/show',         [EventVideoController::class, 'show']);

    });



