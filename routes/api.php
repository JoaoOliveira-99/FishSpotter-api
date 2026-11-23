<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaitController;
use App\Http\Controllers\FishingTypeController;
use App\Http\Controllers\LicenseTypeController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\SpotTypeController;

//Auth
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

//municipalities
Route::get('/municipalities', [MunicipalityController::class, 'index']);
Route::get('/municipalities/{municipality}', [MunicipalityController::class, 'show']);

//spot-types
Route::get('/spot-types', [SpotTypeController::class, 'index']);
Route::get('/spot-types/{spotType}', [SpotTypeController::class, 'show']);

//fishing-types
Route::get('/fishing-types', [FishingTypeController::class, 'index']);
Route::get('/fishing-types/{fishingType}', [FishingTypeController::class, 'show']);

//baits
Route::get('/baits', [BaitController::class, 'index']);
Route::get('/baits/{bait}', [BaitController::class, 'show']);

//licenses
Route::get('/licenses', [LicenseTypeController::class, 'index']);
Route::get('/licenses/{licenseType}', [LicenseTypeController::class, 'show']);


Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});