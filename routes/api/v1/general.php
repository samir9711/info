<?php

use App\Http\Controllers\AboutUs\AboutUsController;
use App\Http\Controllers\Auth\UnifiedAuthController;
use App\Http\Controllers\Currency\CurrencyController;
use App\Http\Controllers\Directorate\DirectorateController;
use App\Http\Controllers\Establishment\EstablishmentController;
use App\Http\Controllers\Ministry\MinistryController;
use App\Http\Controllers\MinistryAccount\MinistryAccountController;
use App\Http\Controllers\PrivacyPolicy\PrivacyPolicyController;
use App\Http\Controllers\TermsCondition\TermsConditionController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;


Route::post('upload/{folder}/single', [UploadController::class, 'single'])
    ->where('folder', '[A-Za-z0-9_-]+');

Route::post('upload/{folder}/multiple', [UploadController::class, 'multiple'])
    ->where('folder', '[A-Za-z0-9_-]+');


    Route::prefix('currency')->group(function () {
        Route::get('/all/paginated', [CurrencyController::class, 'allPaginated']);
        Route::get('/all',           [CurrencyController::class, 'all']);
        Route::post('/show',         [CurrencyController::class, 'show']);
        Route::post('/create',       [CurrencyController::class, 'store']);
        Route::post('/update',       [CurrencyController::class, 'update']);
        Route::post('/activate',     [CurrencyController::class, 'activate']);
        Route::post('/deactivate',   [CurrencyController::class, 'deactivate']);
    });







