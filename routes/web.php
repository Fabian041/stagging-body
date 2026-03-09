<?php

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NidecController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PullingController;
use App\Http\Controllers\ErrorLogController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\LoadingListController;
use App\Http\Controllers\TraceabilityController;
use App\Http\Controllers\DirectPullingSSEController;
use App\Http\Controllers\PisController;

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

// Root route - require authentication
Route::middleware(['auth'])->get('/', [DashboardController::class, 'index'])->name('home');

// unauthencticated user
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login.index');
    Route::post('/login-auth', [LoginController::class, 'authenticate'])->name('login.auth');

    Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
    Route::post('/register-store', [RegisterController::class, 'store'])->name('register.store');

    Route::get('dashboard/receiving', [DashboardController::class, 'receivingDashboard'])->name('dashboard.receiving');
    Route::get('dashboard/receiving/getData', [DashboardController::class, 'getReceivingData'])->name('dashboard.receiving.getData');
    Route::get('dashboard/receiving/detail', [DashboardController::class, 'showModal'])->name('dashboard.receiving.detail');
});

// stream SSE - require authentication
Route::middleware(['auth'])->get('/stream/direct-pulling-updates', [DirectPullingSSEController::class, 'streamDirectPullingUpdates'])
    ->name('sse.direct-pulling-updates');

// PIS Routes - require authentication
Route::middleware(['auth'])->prefix('pis')->group(function () {
    // Scanning Pages
    Route::get('/', [PisController::class, 'index'])->name('pis.index');
    Route::get('/packing', [PisController::class, 'packing'])->name('pis.packing');
    Route::get('/loading-list', [PisController::class, 'loadingList'])->name('pis.loadingList');

    // Core Scanning API
    Route::get('/getAjaxImage/{image}/{type}/{dock}', [PisController::class, 'getAjaxImage'])->name('pis.getAjaxImage');

    // Master Data Management
    Route::get('/master', [PisController::class, 'PisMasterView'])->name('pis.master');
    Route::get('/preview/{img}', [PisController::class, 'PisPreview'])->name('pis.preview');
    Route::post('/search', [PisController::class, 'PisSearch'])->name('pis.search');
    Route::get('/validasi', [PisController::class, 'validasi'])->name('pis.validasi');

    // CRUD Operations
    Route::get('/edit/{id}', [PisController::class, 'UpdatePis'])->name('pis.edit');
    Route::post('/update/{id}', [PisController::class, 'UpdatePisProses'])->name('pis.update');
    Route::get('/delete/{id}', [PisController::class, 'destroy'])->name('pis.delete');
    
    // Add/Update PIS Data
    Route::post('/addpis', [PisController::class, 'addpis'])->name('pis.addpis');
    Route::post('/addpart', [PisController::class, 'addpart'])->name('pis.addpart');
    Route::post('/updatepis', [PisController::class, 'UpdatePisProses'])->name('pis.updatepis');
});

// Additional PIS Routes - require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/getajaxpartPis', [PisController::class, 'GetAjaxPartPis'])->name('pis.getAjaxPartPis');
    // Backward-compatible alias used by some legacy views
    Route::get('/getajaxpart', [PisController::class, 'GetAjaxPartPis']);
    
    // PIS Scan routes
    Route::get('/pis/scan-list', [PisController::class, 'scanList'])->name('pis.scanList');
    Route::post('/pis/save-scan', [PisController::class, 'savePisScan'])->name('pis.saveScan');
    Route::post('/pis/update-scan-detail', [PisController::class, 'updatePisScanDetail'])->name('pis.updateScanDetail');
    Route::post('/pis/get-loading-list-data', [PisController::class, 'getLoadingListData'])->name('pis.getLoadingListData');
    Route::get('/pis/get-scan-list', [PisController::class, 'getPisScanList'])->name('pis.getScanList');
    Route::get('/pis/get-scan-details', [PisController::class, 'getPisScanDetails'])->name('pis.getScanDetails');
    Route::get('/pis/get-scans-by-pds', [PisController::class, 'getPisScansByPds'])->name('pis.getScansByPds');
});

Route::middleware(['auth'])->post('/refresh-token', function () {
    if (Auth::check()) {
        $response = Http::timeout(30)->withoutVerifying()->post('https://dea-dev.aiia.co.id/api/v1/auth/login', [
            'npk' => Auth::user()->npk,
            'password' => '123456'
        ]);

        if ($response->successful()) {
            $token = json_decode($response->body(), true)['data']['access_token'];
            session()->put('token', $token);
            return response()->json(['success' => true, 'message' => 'Token refreshed']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to refresh token'], 500);
    }

    return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
});

// authenticated user
Route::middleware(['auth'])->group(function () {

    Route::get('/stock-dashboard', [StockController::class, 'index'])->name('stocks.dashboard');
    Route::get('/api/stocks/mock', [StockController::class, 'mockData']);
    Route::get('/api/stocks/mock/{line}', [StockController::class, 'mockLineData']);

    Route::prefix('nidec')->group(function () {
        Route::get('/', [NidecController::class, 'index'])->name('nidec.index');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout.auth');

    // kanban
    Route::get('/kanban/check', [PullingController::class, 'kanbanCheck'])->name('kanban.check');
    Route::get('/kanban/afterPull', [PullingController::class, 'kanbanAfterPull'])->name('kanban.afterPull');

    // loading list
    Route::get('/kanban/scanned', [LoadingListController::class, 'kanbanScanned'])->name('kanban.scanned');
    Route::get('/loading-list', [LoadingListController::class, 'index'])->name('loadingList.index');
    Route::delete('/loading-list-detail/{detail}/pulling-mutation/{mutation}', [LoadingListController::class, 'deletePullingMutation']);
    Route::get('/loading-list/{loadingList}', [LoadingListController::class, 'detail'])->name('loadingList.detail');
    Route::prefix('loading-list')->group(function () {
        Route::get('/edit/{loadingList}/{customerPart}/{backNumber}/{newActual}', [LoadingListController::class, 'editLoadingListDetail'])->name('loadingListDetail.edit');
        Route::get('/fetch/{pds}', [LoadingListController::class, 'fetchLoadingList'])->name('loadingList.fetch');
        Route::get('/store/{loadingList}/{pds}/{cycle}/{customerCode}/{deliveryDate}/{shippingDate}', [LoadingListController::class, 'store'])->name('loadingList.store');
        Route::get('/storeDetail/{loadingList}/{customerPart}/{internalPart}/{kbnQty}/{qtyPerKanban}/{totalQty}/{actualKanbanQty}', [LoadingListController::class, 'storeDetail'])->name('loadingList.storeDetail');
    });

    // dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::prefix('dashboard')->group(function () {
        Route::get('/stock/export', [DashboardController::class, 'exportStock'])->name('dashboard.stock.export');
        Route::get('/mutation/export', [DashboardController::class, 'exportMutation'])->name('dashboard.mutation.export');
        // Production stock monitoring (sebelumnya ada di /dashboard)
        Route::get('/production/stock', [DashboardController::class, 'productionStock'])->name('dashboard.productionStock');

        Route::get('/production/landing', [DashboardController::class, 'boardLanding'])->name('board.landing');

        Route::get('/production/result', [DashboardController::class, 'prodResult'])->name('dashboard.prodResult');
        Route::get('/production/plan', [DashboardController::class, 'prodPlan'])->name('dashboard.prodPlan');
        Route::get('/production/board', [DashboardController::class, 'prodBoard'])->name('dashboard.board');
        Route::get('/production/board/state', [DashboardController::class, 'prodBoardState']);

        // datatable
        Route::get('/getLoadingList', [LoadingListController::class, 'getLoadingList'])->name('dashboard.getLoadingList');
        Route::get('/getLoadingListDetail/{loadingList}', [LoadingListController::class, 'getLoadingListDetail'])->name('dashboard.getLoadingListDetail');

        // Add this route to your existing routes
        Route::get('/checkLoadingListUpdates', [LoadingListController::class, 'checkLoadingListUpdates'])->name('dashboard.checkLoadingListUpdates');
        Route::post('/getLoadingListUpdates', [LoadingListController::class, 'getLoadingListUpdates'])->name('dashboard.getLoadingListUpdates');
        Route::get('/getLoadingListsByPds', [LoadingListController::class, 'getLoadingListsByPds'])->name('dashboard.getLoadingListsByPds');

        Route::get('/progressPulling', [DashboardController::class, 'progressPulling'])->name('progressPulling.index');
        Route::post('/part/import', [DashboardController::class, 'importPart'])->name('dashboard.part.import');
        Route::post('/manifest/import', [DashboardController::class, 'importManifest'])->name('dashboard.manifest.import');
        Route::post('/stock/import', [DashboardController::class, 'importStock'])->name('dashboard.stock.import');

        // check kanban
        Route::get('/kanban/check', [DashboardController::class, 'kbnCheck'])->name('dashboard.kbnCheck');
        Route::post('/kanban/check', [DashboardController::class, 'kbnCheckSubmit'])->name('dashboard.kbnCheckSubmit');
    });

    // edcl
    Route::prefix('edcl')->group(function () {
        Route::get('/store/{skid}/{manifest}/{itemNo}/{seqNo}/{customerPart}/{originalBarcode}/{loadingList}/{customer}', [PullingController::class, 'edcl'])->name('store.edcl');
        Route::get('/detail/{loadingListId}/{cutomerPartId}', [LoadingListController::class, 'edclDetail'])->name('detail.edcl');
        Route::get('/cancel/{id}', [PullingController::class, 'edclCancel'])->name('cancel.edcl');
    });

    // production
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::get('/production/prdreport', [ProductionController::class, 'indexprdreport'])->name('production.indexprdreport');
    Route::get('/pc2b', [ProductionController::class, 'pc2b'])->name('pc2b.index');
    Route::prefix('production')->group(function () {
        Route::get('/current-scan-count/{line}', [ProductionController::class, 'getCurrentScanCount']);
        Route::post('/reset-scan-count/{line}', [ProductionController::class, 'resetScanCount']);
        Route::get('/update-scan-target/{line}/{target}', [ProductionController::class, 'updateScanTarget']);
        Route::get('/as523', [ProductionController::class, 'as523'])->name('as523.index');
        Route::get('/line-check/{line}', [ProductionController::class, 'lineCheck'])->name('production.line-check');
        Route::get('/sample-check/{line}/{sample}', [ProductionController::class, 'sampleCheck'])->name('production.sample-check');
        Route::get('/store', [ProductionController::class, 'store'])->name('production.store');
        Route::get('/store2', [ProductionController::class, 'store2'])->name('production.store2'); // prd report
        Route::get('/api-get-internal/{custPart}', [ProductionController::class, 'getInternalPart'])->name('getInternalPart');
        Route::post('/adjust', [ProductionController::class, 'adjust'])->name('production.adjust');
        Route::get('/api-list-stop/{line}/{category}', [ProductionController::class, 'getListStop']);
        Route::post('/api-insert-stop', [ProductionController::class, 'insertStop']);
        Route::post('/api-stop', [ProductionController::class, 'inboundStop']);
        Route::post('/pc2b/scan-kanban', [ProductionController::class, 'storePc2bKanbanScan'])
            ->name('production.pc2b.scan-kanban');

        Route::post('/part-scan', [ProductionController::class, 'storePartScan'])
            ->name('production.part-scan');

        Route::post('/part-scan/assign-kanban', [ProductionController::class, 'assignKanbanToPartScans'])
            ->name('production.assign-kanban');

        Route::get('/direct', [ProductionController::class, 'direct'])->name('production.direct.index');
    });

    //Validation

    Route::get('/validation', [ValidationController::class, 'index'])->name('validation.index');
    Route::prefix('validation')->group(function () {
        Route::get('/kanban/pairing', [ValidationController::class, 'pair'])->name('validation.pairing');
    });
    // pulling
    Route::get('/pulling', [PullingController::class, 'index'])->name('pulling.index');
    Route::prefix('pulling')->group(function () {
        Route::get('/settings', [PullingController::class, 'settingIndex'])
            ->name('pulling.settings');

        Route::post('/settings/update', [PullingController::class, 'settingUpdate'])
            ->name('pulling.settings.update');

        Route::post('/settings/reorder/add', [PullingController::class, 'addManualItem'])
            ->name('pulling.reorder.add');

        Route::post('/settings/reorder', [PullingController::class, 'reorderByDeliveryTime'])
            ->name('pulling.reorder');

        Route::delete('/settings/reorder/{id}', [PullingController::class, 'deleteItem'])
            ->name('pulling.reorder.delete');

        Route::get('/settings/reorder/options', [PullingController::class, 'addItemOptions'])
            ->name('pulling.reorder.options');

        Route::get('/customer-check/{customer}/{pds?}', [PullingController::class, 'customerCheck'])->name('pulling.customer-check');
        // Route::get('/internal-check/{internal}', [PullingController::class, 'internalCheck'])->name('pulling.internal-check');
        Route::get('/internal-check/{internal}/{isinternal?}', [PullingController::class, 'internalCheck'])->name('pulling.internal-check');
        Route::get('/store', [PullingController::class, 'store'])->name('pulling.store');
        Route::get('/post', [PullingController::class, 'post'])->name('pulling.post');
        Route::get('/mutation', [PullingController::class, 'mutation'])->name('pulling.mutation');

        Route::get('/manual', [PullingController::class, 'manual'])->name('pulling.manual');
        Route::post('/manual', [PullingController::class, 'manualReset'])->name('pulling.manualReset');
    });

    // get manifest
    // Route::get('/manifest/{pdsNumber}', [ManifestController::class, 'show'])->name('manifest.show');

    // error log
    Route::prefix('error')->group(function () {
        Route::get('/store', [ErrorLogController::class, 'store'])->name('error.store');
        Route::get('/log', [ErrorLogController::class, 'index'])->name('error.log');
        Route::get('/getErrorLogs', [ErrorLogController::class, 'getErrorLogs'])->name('error.getErrorLogs');
    });

    Route::get('/test', [ProductionController::class, 'test'])->name('test');
    
    // PIS Dashboard Routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/viewDashboardMutation', [PisController::class, 'viewDashboardMutation'])->name('dashboard.viewDashboardMutation');
        Route::get('/getAjaxMutation', [PisController::class, 'getAjaxMutation'])->name('dashboard.getAjaxMutation');
    });
});
