<?php


use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\CompanyContactInfo\CompanyContactInfoController;
use App\Http\Controllers\CompanyGalleryImage\CompanyGalleryImageController;
use App\Http\Controllers\CompanyJob\CompanyJobController;
use App\Http\Controllers\CompanyJobApplication\CompanyJobApplicationController;
use App\Http\Controllers\CompanyRecommendedCourse\CompanyRecommendedCourseController;
use App\Http\Controllers\CompanySection\CompanySectionController;
use App\Http\Controllers\CompanySkill\CompanySkillController;
use App\Http\Controllers\Skill\SkillController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('company')->group(function () {

 Route::post('login', [CompanyAuthController::class, 'login']);

    Route::middleware('auth:company')->group(function () {
        Route::post('logout', [CompanyAuthController::class, 'logout']);
    });

    Route::prefix('contact-info')->middleware('auth:company')->group(function () {
        Route::post('/show',         [CompanyContactInfoController::class, 'show']);
        Route::post('/create',       [CompanyContactInfoController::class, 'store']);

    });

    Route::prefix('company-section')->middleware('auth:company')->group(function () {
        Route::get('/all/paginated', [CompanySectionController::class, 'allPaginated']);
        Route::get('/all',           [CompanySectionController::class, 'all']);
        Route::post('/show',         [CompanySectionController::class, 'show']);
        Route::post('/create',       [CompanySectionController::class, 'store']);
        Route::post('/update',       [CompanySectionController::class, 'update']);
        Route::delete('/delete',     [CompanySectionController::class, 'destroy']);
    });


    Route::prefix('gallery-image')->middleware('auth:company')->group(function () {
        Route::get('/all/paginated', [CompanyGalleryImageController::class, 'allPaginated']);
        Route::get('/all',           [CompanyGalleryImageController::class, 'all']);
        Route::post('/show',         [CompanyGalleryImageController::class, 'show']);
        Route::post('/create',       [CompanyGalleryImageController::class, 'store']);
        Route::post('/update',       [CompanyGalleryImageController::class, 'update']);
        Route::delete('/delete',     [CompanyGalleryImageController::class, 'destroy']);
    });


    Route::prefix('recommended-course')->middleware('auth:company')->group(function () {
        Route::get('/all/paginated', [CompanyRecommendedCourseController::class, 'allPaginated']);
        Route::get('/all',           [CompanyRecommendedCourseController::class, 'all']);
        Route::post('/show',         [CompanyRecommendedCourseController::class, 'show']);
        Route::post('/create',       [CompanyRecommendedCourseController::class, 'store']);
        Route::post('/update',       [CompanyRecommendedCourseController::class, 'update']);
        Route::delete('/delete',     [CompanyRecommendedCourseController::class, 'destroy']);
    });

    Route::prefix('company-skill')->middleware('auth:company')->group(function () {
        Route::get('/all/paginated', [CompanySkillController::class, 'allPaginated']);
        Route::get('/all',           [CompanySkillController::class, 'all']);
        Route::post('/show',         [CompanySkillController::class, 'show']);
        Route::post('/create',       [CompanySkillController::class, 'store']);
        Route::post('/update',       [CompanySkillController::class, 'update']);
        Route::delete('/delete',     [CompanySkillController::class, 'destroy']);
    });

    Route::prefix('skill')->middleware('auth:company')->group(function () {
        Route::get('/all/paginated', [SkillController::class, 'allPaginated']);
        Route::get('/all',           [SkillController::class, 'all']);
        Route::post('/show',         [SkillController::class, 'show']);

    });

    Route::prefix('company-job')->middleware('auth:company')->group(function () {
        Route::get('/all/paginated', [CompanyJobController::class, 'allPaginated']);
        Route::get('/all',           [CompanyJobController::class, 'all']);
        Route::post('/show',         [CompanyJobController::class, 'show']);
        Route::post('/create',       [CompanyJobController::class, 'store']);
        Route::post('/update',       [CompanyJobController::class, 'update']);
        Route::delete('/delete',     [CompanyJobController::class, 'destroy']);
    });

    Route::middleware('auth:company')->group(function () {
        Route::post('/profile',      [CompanyController::class, 'updateProfile']);
        Route::get('/my-profile',    [CompanyController::class, 'myProfile']);
    });

     Route::prefix('user')->middleware('auth:company')->group(function () {
        Route::get('/all/paginated', [UserController::class, 'allPaginated']);
        Route::get('/all',           [UserController::class, 'all']);
        Route::post('/show',         [UserController::class, 'show']);

    });


    Route::prefix('company-job-application')->middleware('auth:company')->group(function () {
        Route::get('/my-applications',[CompanyJobApplicationController::class, 'companyApplications']);
        Route::post('/change-status', [CompanyJobApplicationController::class, 'changeStatus']);
    });
});

