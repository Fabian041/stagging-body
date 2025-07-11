<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\API\LoginController as APILoginController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function () {
    Route::get('/scan/{line}', [ProductionController::class, 'scan']);
    Route::post('/injection', [ProductionController::class , 'post']);
    Route::post('/import', [ProductionController::class , 'import']);
    Route::post('/login', [APILoginController::class , 'authenticate']);
    
});