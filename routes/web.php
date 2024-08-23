<?php

use Illuminate\Support\Facades\Route;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PullingController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\LoadingListController;
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
    return redirect()->intended(RouteServiceProvider::HOME);
})->middleware('auth');

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

    // kanban
    Route::get('/kanban/check', [PullingController::class, 'kanbanCheck'])->name('kanban.check');
    Route::get('/kanban/afterPull', [PullingController::class, 'kanbanAfterPull'])->name('kanban.afterPull');
    
    // loading list
    Route::get('/kanban/scanned', [LoadingListController::class, 'kanbanScanned'])->name('kanban.scanned');
    Route::get('/loading-list', [LoadingListController::class, 'index'])->name('loadingList.index');
    Route::get('/loading-list/{loadingList}', [LoadingListController::class, 'detail'])->name('loadingList.detail');
    Route::prefix('loading-list')->group(function(){
        Route::get('/edit/{loadingList}/{customerPart}/{backNumber}/{newActual}', [LoadingListController::class, 'editLoadingListDetail'])->name('loadingListDetail.edit');
        Route::get('/fetch/{pds}', [LoadingListController::class, 'fetchLoadingList'])->name('loadingList.fetch');
        Route::get('/store/{loadingList}/{pds}/{cycle}/{customerCode}/{deliveryDate}/{shippingDate}', [LoadingListController::class, 'store'])->name('loadingList.store');
        Route::get('/storeDetail/{loadingList}/{customerPart}/{internalPart}/{kbnQty}/{qtyPerKanban}/{totalQty}/{actualKanbanQty}', [LoadingListController::class, 'storeDetail'])->name('loadingList.storeDetail');
    });

    // dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::prefix('dashboard')->group(function(){
        
        // datatable
        Route::get('/getLoadingList', [LoadingListController::class, 'getLoadingList'])->name('dashboard.getLoadingList');
        Route::get('/getLoadingListDetail/{loadingList}', [LoadingListController::class, 'getLoadingListDetail'])->name('dashboard.getLoadingListDetail');
        
        Route::get('/progressPulling', [DashboardController::class, 'progressPulling'])->name('progressPulling.index');
        Route::post('/part/import', [DashboardController::class, 'importPart'])->name('dashboard.part.import');
        Route::post('/manifest/import', [DashboardController::class, 'importManifest'])->name('dashboard.manifest.import');
        Route::post('/stock/import', [DashboardController::class, 'importStock'])->name('dashboard.stock.import');
    });

    // production
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::prefix('production')->group(function(){
        Route::get('/line-check/{line}', [ProductionController::class, 'lineCheck'])->name('production.line-check');
        Route::get('/sample-check/{line}/{sample}', [ProductionController::class, 'sampleCheck'])->name('production.sample-check');
        Route::get('/store', [ProductionController::class, 'store'])->name('production.store');
        Route::post('/adjust', [ProductionController::class, 'adjust'])->name('production.adjust');
    });

    // pulling
    Route::get('/pulling', [PullingController::class, 'index'])->name('pulling.index');
    Route::prefix('pulling')->group(function(){
        Route::get('/customer-check/{customer}', [PullingController::class, 'customerCheck'])->name('pulling.customer-check');
        Route::get('/internal-check/{internal}', [PullingController::class, 'internalCheck'])->name('pulling.internal-check');
        Route::get('/store', [PullingController::class, 'store'])->name('pulling.store');
        Route::get('/post', [PullingController::class, 'post'])->name('pulling.post');
        Route::get('/mutation', [PullingController::class, 'mutation'])->name('pulling.mutation');
    });

    // get manifest
    // Route::get('/manifest/{pdsNumber}', [ManifestController::class, 'show'])->name('manifest.show');

    // error log
    Route::prefix('error')->group(function(){
        Route::get('/store', [ErrorLogController::class, 'error'])->name('error.store');
    });

    Route::get('/test', [ProductionController::class, 'test'])->name('test');
});
