<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TraceabilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('layouts.auth.login');
})->middleware('guest');

// unauthencticated user
Route::middleware(['guest'])->group(function () {

    Route::get('/login', [LoginController::class, 'index'])->name('login.index');
    Route::post('/login-auth', [LoginController::class, 'authenticate'])->name('login.auth');
    Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
    Route::post('/register-store', [RegisterController::class, 'store'])->name('register.store');
});

// authenticated user
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout.auth');

    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::get('/production/line-check/{line}', [ProductionController::class, 'lineCheck'])->name('production.line-check');
    Route::get('/production/sample-check/{line}/{sample}', [ProductionController::class, 'sampleCheck'])->name('production.sample-check');

    // insert
    Route::get('/production/store', [ProductionController::class, 'store'])->name('production.store');

});
