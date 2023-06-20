<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PullingController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\TraceabilityController;

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

    // dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/dashboard/part/import', [DashboardController::class, 'importPart'])->name('dashboard.part.import');
    Route::post('/dashboard/manifest/import', [DashboardController::class, 'importManifest'])->name('dashboard.manifest.import');

    // production
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::get('/production/line-check/{line}', [ProductionController::class, 'lineCheck'])->name('production.line-check');
    Route::get('/production/sample-check/{line}/{sample}', [ProductionController::class, 'sampleCheck'])->name('production.sample-check');
    Route::get('/production/store', [ProductionController::class, 'store'])->name('production.store');

    // pulling
    Route::get('/pulling', [PullingController::class, 'index'])->name('pulling.index');
    Route::get('/pulling/customer-check/{customer}', [PullingController::class, 'customerCheck'])->name('pulling.customer-check');
    Route::get('/pulling/internal-check/{internal}', [PullingController::class, 'internalCheck'])->name('pulling.internal-check');
    Route::get('/pulling/store', [PullingController::class, 'store'])->name('pulling.store');
    Route::get('/pulling/post', [PullingController::class, 'post'])->name('pulling.post');

    // get manifest
    Route::get('/manifest/{pdsNumber}', [ManifestController::class, 'show'])->name('manifest.show');

    // error log
    Route::prefix('error')->group(function(){
        Route::get('/store', [ErrorLogController::class, 'error'])->name('error.store');
    });
});
