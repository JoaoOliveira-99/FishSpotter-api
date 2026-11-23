<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\SpotTypeController;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

Route::get('/municipalities', [MunicipalityController::class, 'index']);
Route::get('/municipalities/{municipality}', [MunicipalityController::class, 'show']);

Route::get('/spot-types', [SpotTypeController::class, 'index']);
Route::get('/spot-types/{spotType}', [SpotTypeController::class, 'show']);


Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});