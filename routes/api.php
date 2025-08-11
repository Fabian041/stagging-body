<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\API\LoginController as APILoginController;
use App\Http\Controllers\API\ProductionPlanApiController as ProductionPlan;

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

Route::get('/production-items', function(Request $request) {
    $date = $request->input('date');
    $line = $request->input('line');
    
    if (!$date || !$line) {
        return response()->json(['message' => 'Date and line are required'], 400);
    }

    $items = \App\Models\ProductionPlan::where('plan_date', $date)
        ->where('line', $line)
        // ->orderBy('delivery_time')
        ->get(['id', 'customer', 'back_no', 'order_qty', 'delivery_time', 'working_start']);

    return response()->json($items);
});

Route::group(['prefix' => 'v1'], function () {
    Route::get('/scan/{line}', [ProductionController::class, 'scan']);
    Route::post('/injection', [ProductionController::class , 'post']);
    Route::post('/import', [ProductionController::class , 'import']);
    Route::post('/login', [APILoginController::class , 'authenticate']);

    Route::group(['prefix' => 'production-plan'], function(){
        // production plan
        Route::post('/update-qty', [ProductionPlan::class, 'updateQty']);
    });
});