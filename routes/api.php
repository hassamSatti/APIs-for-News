<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\UserNewsPreferenceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('reset-token', [AuthController::class, 'generateResetToken']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
     
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
}); 

Route::middleware('auth:sanctum')->group(function () {
    Route::get('news', [NewsController::class, 'index']);
    Route::get('news/{id}', [NewsController::class, 'show']);
    Route::post('preferences', [UserNewsPreferenceController::class, 'addOrUpdate']);
    Route::get('preferences', [UserNewsPreferenceController::class, 'getPreferences']);
    Route::get('personalizedNews', [UserNewsPreferenceController::class, 'getNewsBasedOnPreferences']);
});