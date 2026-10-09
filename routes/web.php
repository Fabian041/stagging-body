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
use App\Http\Controllers\InjectionController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\LoadingListController;
use App\Http\Controllers\LeakController;
use App\Http\Controllers\TraceabilityController;
use App\Http\Controllers\DirectPullingSSEController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\PisController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\MachineAuthorizationAdminController;

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

// Root route - portal (launcher) untuk semua: guest & user login. Tombol Login hilang saat sudah login.
Route::get('/', [DashboardController::class, 'index'])->name('home');

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
    /** Gambar PIS dari disk `pis` (nama file boleh ber-spasi; tidak bergantung pada URL static / storage:link). */
    Route::get('/storage-image', [PisController::class, 'storageImage'])->name('pis.storageImage');

    // Master Data Management
    Route::get('/master', [PisController::class, 'PisMasterView'])->name('pis.master');
    Route::get('/preview/{img}', [PisController::class, 'PisPreview'])->name('pis.preview');
    Route::post('/search', [PisController::class, 'PisSearch'])->name('pis.search');
    Route::get('/validasi', [PisController::class, 'validasi'])->name('pis.validasi');

    // CRUD Operations
    Route::get('/edit/{id}', [PisController::class, 'UpdatePis'])->name('pis.edit');
    Route::post('/update/{id}', [PisController::class, 'UpdatePisProses'])->name('pis.update');
    Route::get('/delete/{id}', [PisController::class, 'destroy'])->middleware('can:manage-master')->name('pis.delete');

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
    // Route legacy (method belum dipakai / belum ada implementasinya). Simpan dulu agar tidak error 500.
    // Jika perlu history scan per label, akan ditambahkan endpoint yang membaca `pis_scan_logs`.
    // Route::get('/pis/get-scan-detail-history', [PisController::class, 'getPisScanDetailHistory'])->name('pis.getScanDetailHistory');
    Route::get('/pis/get-scans-by-pds', [PisController::class, 'getPisScansByPds'])->name('pis.getScansByPds');
    Route::get('/pis/scan-list/export', [PisController::class, 'exportPisScanList'])->name('pis.scanListExport');
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

Route::middleware(['auth'])->get('/global-search', [GlobalSearchController::class, 'search'])->name('global-search');

// authenticated user
Route::middleware(['auth'])->group(function () {

    Route::get('/stock-dashboard', [StockController::class, 'index'])->name('stocks.dashboard');
    Route::get('/api/stocks/mock', [StockController::class, 'mockData']);
    Route::get('/api/stocks/mock/{line}', [StockController::class, 'mockLineData']);

    Route::prefix('nidec')->group(function () {
        Route::get('/', [NidecController::class, 'index'])->name('nidec.index');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout.auth');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/settings', [SettingsController::class, 'index'])->middleware('can:access-settings')->name('settings.index');
    Route::put('/settings/preferences', [SettingsController::class, 'update'])->middleware('can:access-settings')->name('settings.update');
    Route::get('/manage-devices', [SettingsController::class, 'devices'])
        ->middleware('can:manage-device-sessions')
        ->name('manage-devices.index');
    Route::get('/settings/devices/monitor', [SettingsController::class, 'deviceMonitor'])
        ->middleware('can:manage-device-sessions')
        ->name('settings.devices.monitor');
    Route::post('/settings/devices/heartbeat', [SettingsController::class, 'deviceHeartbeat'])->name('settings.devices.heartbeat');
    Route::post('/settings/devices/reset-applied', [SettingsController::class, 'deviceResetApplied'])->name('settings.devices.reset-applied');
    Route::post('/settings/devices/error', [SettingsController::class, 'reportDeviceError'])->name('settings.devices.error');
    Route::post('/settings/devices/{device}/label', [SettingsController::class, 'updateDeviceLabel'])
        ->middleware('can:manage-device-sessions')
        ->name('settings.devices.label');
    Route::post('/settings/devices/{device}/reset', [SettingsController::class, 'resetDevice'])
        ->middleware('can:manage-device-sessions')
        ->name('settings.devices.reset');
    Route::delete('/settings/devices/{device}', [SettingsController::class, 'deleteDevice'])
        ->middleware('can:manage-device-sessions')
        ->name('settings.devices.delete');
    Route::prefix('manage-users')->name('manage-users.')->middleware('can:manage-users')->group(function () {
        Route::get('/', [ManageUserController::class, 'index'])->name('index');
        Route::post('/', [ManageUserController::class, 'store'])->name('store');
        Route::put('/{user}', [ManageUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [ManageUserController::class, 'destroy'])->name('destroy');
        Route::get('/template/download', [ManageUserController::class, 'template'])->name('template');
        Route::post('/bulk-upload', [ManageUserController::class, 'bulkUpload'])->name('bulk-upload');
    });

    Route::prefix('machine-authorization')
        ->name('machine-auth.')
        ->middleware('can:manage-machine-auth')
        ->group(function () {
            Route::get('/', [MachineAuthorizationAdminController::class, 'index'])->name('index');
            Route::get('/rfid-cards/data', [MachineAuthorizationAdminController::class, 'cardsData'])->name('cards.data');
            Route::post('/rfid-cards', [MachineAuthorizationAdminController::class, 'storeCard'])->name('cards.store');
            Route::put('/rfid-cards/{rfidCard}', [MachineAuthorizationAdminController::class, 'updateCard'])->name('cards.update');
            Route::patch('/rfid-cards/{rfidCard}/toggle', [MachineAuthorizationAdminController::class, 'toggleCard'])->name('cards.toggle');

            Route::get('/machines/data', [MachineAuthorizationAdminController::class, 'machinesData'])->name('machines.data');
            Route::post('/machines', [MachineAuthorizationAdminController::class, 'storeMachine'])->name('machines.store');
            Route::put('/machines/{machine}', [MachineAuthorizationAdminController::class, 'updateMachine'])->name('machines.update');
            Route::patch('/machines/{machine}/toggle', [MachineAuthorizationAdminController::class, 'toggleMachine'])->name('machines.toggle');
            Route::post('/machines/{machine}/tokens', [MachineAuthorizationAdminController::class, 'createToken'])->name('machines.tokens.store');
            Route::delete('/machines/{machine}/tokens/{token}', [MachineAuthorizationAdminController::class, 'revokeToken'])->name('machines.tokens.destroy');

            Route::get('/permissions/data', [MachineAuthorizationAdminController::class, 'permissionsData'])->name('permissions.data');
            Route::post('/permissions', [MachineAuthorizationAdminController::class, 'storePermission'])->name('permissions.store');
            Route::put('/permissions/{machinePermission}', [MachineAuthorizationAdminController::class, 'updatePermission'])->name('permissions.update');
            Route::delete('/permissions/{machinePermission}', [MachineAuthorizationAdminController::class, 'destroyPermission'])->name('permissions.destroy');

            Route::get('/access-logs/data', [MachineAuthorizationAdminController::class, 'logsData'])->name('logs.data');
        });

    // kanban
    Route::get('/kanban/check', [PullingController::class, 'kanbanCheck'])->name('kanban.check');
    Route::get('/kanban/afterPull', [PullingController::class, 'kanbanAfterPull'])->name('kanban.afterPull');

    // loading list
    Route::get('/kanban/scanned', [LoadingListController::class, 'kanbanScanned'])->name('kanban.scanned');
    Route::get('/loading-list', [LoadingListController::class, 'index'])->name('loadingList.index');
    Route::get('/loading-list/manifests', [LoadingListController::class, 'searchManifests'])->name('loadingList.manifests');
    Route::get('/loading-list/absen-driver', [LoadingListController::class, 'driverAbsen'])->name('loadingList.driverAbsen');
    Route::get('/loading-list/absen-driver/data', [LoadingListController::class, 'getDriverAbsenData'])->name('loadingList.driverAbsen.data');
    Route::post('/loading-list/absen-driver/scan-customer', [LoadingListController::class, 'scanDriverAbsenCustomer'])->name('loadingList.driverAbsen.scanCustomer');
    Route::get('/loading-list/absen-driver/cycles', [LoadingListController::class, 'getDriverAbsenCycles'])->name('loadingList.driverAbsen.cycles');
    Route::post('/loading-list/absen-driver/submit', [LoadingListController::class, 'submitDriverAbsen'])->name('loadingList.driverAbsen.submit');
    Route::get('/loading-list-detail/{detail}/comparison', [LoadingListController::class, 'getLoadingListDetailComparison'])
        ->name('loadingListDetail.comparison');
    Route::delete('/loading-list-detail/{detail}/pulling-mutation/{mutation}', [LoadingListController::class, 'deletePullingMutation'])->middleware('can:manage-master');
    Route::get('/loading-list/{loadingList}', [LoadingListController::class, 'detail'])->name('loadingList.detail');
    Route::delete('/loading-list/{loadingList}', [LoadingListController::class, 'destroy'])->middleware('can:manage-master')->name('loadingList.destroy');
    Route::prefix('loading-list')->group(function () {
        Route::get('/edit/{loadingList}/{customerPart}/{backNumber}/{newActual}', [LoadingListController::class, 'editLoadingListDetail'])->name('loadingListDetail.edit');
        Route::get('/fetch/{pds}', [LoadingListController::class, 'fetchLoadingList'])->name('loadingList.fetch');
        Route::get('/store/{loadingList}/{pds}/{cycle}/{customerCode}/{deliveryDate}/{shippingDate}', [LoadingListController::class, 'store'])->name('loadingList.store');
        Route::get('/storeDetail/{loadingList}/{customerPart}/{internalPart}/{kbnQty}/{qtyPerKanban}/{totalQty}/{actualKanbanQty}', [LoadingListController::class, 'storeDetail'])->name('loadingList.storeDetail');
        Route::post('/storeDetails', [LoadingListController::class, 'storeDetails'])->name('loadingList.storeDetails');
    });

    // LEAK (monitoring, struktur menu mirip Upload Kanban)
    Route::get(
        '/dashboard/leak',
        [
            LeakController::class,
            'index'
        ]
    )
    ->name('dashboard.leak');

    Route::get(
        '/dashboard/leak/machines',
        [
            LeakController::class,
            'machines'
        ]
    )
    ->name('dashboard.leak.machines');

    Route::get(
        '/dashboard/leak/data',
        [
            LeakController::class,
            'data'
        ]
    )
    ->name('dashboard.leak.data');

    // UPLOAD KANBAN
    Route::get(
        '/dashboard/kanban/upload',
        [
            DashboardController::class,
            'kanbanUpload'
        ]
    )
    ->name('dashboard.kanbanUpload');



    Route::post(
        '/dashboard/kanban/upload/process',
        [
            DashboardController::class,
            'kanbanUploadProcess'
        ]
    )
    ->name('dashboard.kanbanUploadProcess');

    // ALL manual crop (garis merah draggable) — TERISOLASI, tidak dipakai TMMIN/MMKI.
    Route::post(
        '/dashboard/kanban/all/process-manual',
        [
            DashboardController::class,
            'kanbanAllManualProcess'
        ]
    )
    ->name('dashboard.kanbanAllManualProcess');

    // ALL manual file raksasa: upload per bagian (chunk) — TERISOLASI.
    Route::post(
        '/dashboard/kanban/all/upload-chunk',
        [
            DashboardController::class,
            'kanbanAllChunkUpload'
        ]
    )
    ->name('dashboard.kanbanAllChunkUpload');

    Route::post(
        '/dashboard/kanban/all/finalize-chunks',
        [
            DashboardController::class,
            'kanbanAllChunksFinalize'
        ]
    )
    ->name('dashboard.kanbanAllChunksFinalize');

    Route::get(
        '/dashboard/kanban/download/{file}',
        [
            DashboardController::class,
            'kanbanDownload'
        ]
    )
    ->name('dashboard.kanbanDownload');

    Route::get(
        '/dashboard/kanban/preview/{file}',
        [
            DashboardController::class,
            'kanbanPreview'
        ]
    )
    ->name('dashboard.kanbanPreview');

    // Arsip kanban permanen (kanban-source) — dipakai popup bar PDS di dashboard delivery.
    Route::get(
        '/dashboard/kanban/source-files',
        [
            DashboardController::class,
            'kanbanSourceFiles'
        ]
    )
    ->name('dashboard.kanbanSourceFiles');

    Route::get(
        '/dashboard/kanban/source-download/{file}',
        [
            DashboardController::class,
            'kanbanSourceDownload'
        ]
    )
    ->name('dashboard.kanbanSourceDownload');

    Route::get(
        '/dashboard/kanban/source-preview/{file}',
        [
            DashboardController::class,
            'kanbanSourcePreview'
        ]
    )
    ->name('dashboard.kanbanSourcePreview');

    Route::get(
        '/dashboard/kanban/status/{id}',
        [
            DashboardController::class,
            'kanbanUploadStatus'
        ]
    )
    ->name('dashboard.kanbanStatus');

    Route::post(
        '/dashboard/kanban/cancel/{id}',
        [
            DashboardController::class,
            'kanbanCancel'
        ]
    )
    ->name('dashboard.kanbanCancel');

    // MASTER PRESET garis manual (ALL/MANUAL) — shared via database.
    // List + save terbuka untuk semua user login; hapus khusus manage-master.
    Route::get(
        '/dashboard/kanban/manual-presets',
        [
            DashboardController::class,
            'kanbanManualPresets'
        ]
    )
    ->name('dashboard.kanbanManualPresets');

    Route::post(
        '/dashboard/kanban/manual-presets',
        [
            DashboardController::class,
            'kanbanManualPresetStore'
        ]
    )
    ->name('dashboard.kanbanManualPresetStore');

    Route::delete(
        '/dashboard/kanban/manual-presets/{preset}',
        [
            DashboardController::class,
            'kanbanManualPresetDestroy'
        ]
    )
    ->middleware('can:manage-master')
    ->name('dashboard.kanbanManualPresetDestroy');

    // dashboard

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/overview', [DashboardController::class, 'overview'])->name('dashboard.overview');
        Route::get('/overview/data', [DashboardController::class, 'overviewData'])->name('dashboard.overview.data');
        Route::post('/overview/ai-analyst', [DashboardController::class, 'overviewAiAnalyst'])->name('dashboard.overview.ai');
        Route::get('/bella/notifications', [DashboardController::class, 'bellaNotifications'])->name('dashboard.bella.notifications');
        Route::get('/overview/slot', [DashboardController::class, 'overviewSlot'])->name('dashboard.overview.slot');
        Route::get('/stock/export', [DashboardController::class, 'exportStock'])->name('dashboard.stock.export');
        Route::get('/mutation/export', [DashboardController::class, 'exportMutation'])->name('dashboard.mutation.export');
        // Production stock monitoring (sebelumnya ada di /dashboard)
        Route::get('/production/stock', [DashboardController::class, 'productionStock'])->name('dashboard.productionStock');
        Route::get('/production/stock/data', [DashboardController::class, 'productionStockData'])->name('dashboard.productionStock.data');
        Route::get('/production/pairing-logs', [DashboardController::class, 'productionPairingLogs'])->name('dashboard.productionPairingLogs');

        Route::prefix('master')->group(function () {
            Route::get('/', [MasterDataController::class, 'index'])->name('dashboard.master.index');

            Route::get('/lines', [MasterDataController::class, 'lines'])->name('dashboard.master.lines.index');
            Route::get('/lines/data', [MasterDataController::class, 'linesData'])->name('dashboard.master.lines.data');
            Route::post('/lines', [MasterDataController::class, 'storeLine'])->name('dashboard.master.lines.store');
            Route::put('/lines/{line}', [MasterDataController::class, 'updateLine'])->name('dashboard.master.lines.update');
            Route::patch('/lines/{line}/toggle', [MasterDataController::class, 'toggleLine'])->name('dashboard.master.lines.toggle');

            Route::get('/molds', [MasterDataController::class, 'molds'])->name('dashboard.master.molds.index');
            Route::get('/molds/data', [MasterDataController::class, 'moldsData'])->name('dashboard.master.molds.data');
            Route::post('/molds', [MasterDataController::class, 'storeMold'])->name('dashboard.master.molds.store');
            Route::put('/molds/{mold}', [MasterDataController::class, 'updateMold'])->name('dashboard.master.molds.update');
            Route::delete('/molds/{mold}', [MasterDataController::class, 'destroyMold'])->name('dashboard.master.molds.destroy');

            Route::get('/parts', [MasterDataController::class, 'internalParts'])->name('dashboard.master.parts.index');
            Route::get('/parts/data', [MasterDataController::class, 'internalPartsData'])->name('dashboard.master.parts.data');
            Route::post('/parts', [MasterDataController::class, 'storeInternalPart'])->name('dashboard.master.parts.store');
            Route::put('/parts/{internalPart}', [MasterDataController::class, 'updateInternalPart'])->name('dashboard.master.parts.update');
            Route::delete('/parts/{internalPart}', [MasterDataController::class, 'destroyInternalPart'])->name('dashboard.master.parts.destroy');

            Route::get('/customer-parts', [MasterDataController::class, 'customerParts'])->name('dashboard.master.customer-parts.index');
            Route::get('/customer-parts/data', [MasterDataController::class, 'customerPartsData'])->name('dashboard.master.customer-parts.data');
            Route::post('/customer-parts', [MasterDataController::class, 'storeCustomerPart'])->name('dashboard.master.customer-parts.store');
            Route::put('/customer-parts/{customerPart}', [MasterDataController::class, 'updateCustomerPart'])->name('dashboard.master.customer-parts.update');
            Route::delete('/customer-parts/{customerPart}', [MasterDataController::class, 'destroyCustomerPart'])->name('dashboard.master.customer-parts.destroy');

            Route::get('/kanban-pairings', [MasterDataController::class, 'kanbanPairings'])->name('dashboard.master.kanban-pairings.index');
            Route::get('/kanban-pairings/data', [MasterDataController::class, 'kanbanPairingsData'])->name('dashboard.master.kanban-pairings.data');
            Route::post('/kanban-pairings', [MasterDataController::class, 'storeKanbanPairing'])->name('dashboard.master.kanban-pairings.store');
            Route::put('/kanban-pairings/{kanbanPairing}', [MasterDataController::class, 'updateKanbanPairing'])->name('dashboard.master.kanban-pairings.update');
            Route::delete('/kanban-pairings/{kanbanPairing}', [MasterDataController::class, 'destroyKanbanPairing'])->name('dashboard.master.kanban-pairings.destroy');
        });

        Route::get('/production/landing', [DashboardController::class, 'boardLanding'])->name('board.landing');

        Route::get('/production/result', [DashboardController::class, 'prodResult'])->name('dashboard.prodResult');
        Route::get('/production/result/detail', [DashboardController::class, 'prodResultDetail'])->name('dashboard.prodResult.detail');
        Route::get('/production/plan', [DashboardController::class, 'prodPlan'])->name('dashboard.prodPlan');
        Route::get('/production/board', [DashboardController::class, 'prodBoard'])->name('dashboard.board');
        Route::get('/production/board/state', [DashboardController::class, 'prodBoardState']);

        // datatable
        Route::get('/delivery', [LoadingListController::class, 'dashboardDelivery'])->name('dashboard.delivery');
        Route::get('/delivery/data', [LoadingListController::class, 'getDeliveryDashboardData'])->name('dashboard.delivery.data');
        Route::post('/delivery/sync-loading-lists', [LoadingListController::class, 'syncDeliveryLoadingLists'])->middleware('can:manage-master')->name('dashboard.delivery.syncLoadingLists');
        Route::get('/delivery/chart-data', [LoadingListController::class, 'getDeliveryCycleChartByFilters'])->name('dashboard.delivery.chartData');
        Route::get('/delivery/customer-cycle-chart', [LoadingListController::class, 'getDeliveryCustomerCycleChartData'])->name('dashboard.delivery.customerCycleChartData');
        Route::get('/delivery/cycle-progress', [LoadingListController::class, 'getDeliveryCycleProgress'])->name('dashboard.delivery.cycleProgress');
        Route::get('/delivery/stacked-chart', [LoadingListController::class, 'getDeliveryStackedChartData'])->name('dashboard.delivery.stackedChart');
        Route::post('/delivery/notify-unfinished', [LoadingListController::class, 'sendDeliveryUnfinishedWaNotification'])->name('dashboard.delivery.notifyUnfinished');
        Route::get('/delivery/master-cycles', [LoadingListController::class, 'getMasterCycles'])->name('dashboard.delivery.masterCycles.index');
        Route::post('/delivery/master-cycles', [LoadingListController::class, 'storeMasterCycle'])->middleware('can:manage-master')->name('dashboard.delivery.masterCycles.store');
        Route::put('/delivery/master-cycles/{masterCycle}', [LoadingListController::class, 'updateMasterCycle'])->middleware('can:manage-master')->name('dashboard.delivery.masterCycles.update');
        Route::delete('/delivery/master-cycles/{masterCycle}', [LoadingListController::class, 'destroyMasterCycle'])->middleware('can:manage-master')->name('dashboard.delivery.masterCycles.destroy');

        Route::get('/getLoadingList', [LoadingListController::class, 'getLoadingList'])->name('dashboard.getLoadingList');
        Route::get('/getLoadingListDetail/{loadingList}', [LoadingListController::class, 'getLoadingListDetail'])->name('dashboard.getLoadingListDetail');

        // Add this route to your existing routes
        Route::get('/checkLoadingListUpdates', [LoadingListController::class, 'checkLoadingListUpdates'])->name('dashboard.checkLoadingListUpdates');
        Route::post('/getLoadingListUpdates', [LoadingListController::class, 'getLoadingListUpdates'])->name('dashboard.getLoadingListUpdates');
        Route::get('/getLoadingListsByPds', [LoadingListController::class, 'getLoadingListsByPds'])->name('dashboard.getLoadingListsByPds');

        Route::get('/progressPulling', [DashboardController::class, 'progressPulling'])->name('progressPulling.index');
        Route::get('/part/template', [DashboardController::class, 'downloadPartImportTemplate'])->name('dashboard.part.template');
        Route::post('/part/import', [DashboardController::class, 'importPart'])->name('dashboard.part.import');
        Route::post('/manifest/import', [DashboardController::class, 'importManifest'])->name('dashboard.manifest.import');
        Route::get('/stock/template', [DashboardController::class, 'downloadStockImportTemplate'])->name('dashboard.stock.template');
        Route::post('/stock/import', [DashboardController::class, 'importStock'])->name('dashboard.stock.import');

        // check kanban
        Route::get('/kanban/check', [DashboardController::class, 'kbnCheck'])->name('dashboard.kbnCheck');
        Route::post('/kanban/check', [DashboardController::class, 'kbnCheckSubmit'])->name('dashboard.kbnCheckSubmit');

        Route::get('/part/check', [DashboardController::class, 'partCheck'])->name('dashboard.partCheck');
        Route::post('/part/check', [DashboardController::class, 'partCheckSubmit'])->name('dashboard.partCheckSubmit');
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
        Route::get('/as547', [ProductionController::class, 'as547'])->name('as547.index');
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
        Route::post('/as547/scan-kanban', [ProductionController::class, 'storeAs547KanbanScan'])
            ->name('production.as547.scan-kanban');
        Route::get('/as547/pair', [ProductionController::class, 'as547Pair'])
            ->name('production.as547.pair');

        Route::post('/part-scan', [ProductionController::class, 'storePartScan'])
            ->name('production.part-scan');

        Route::post('/part-scan/assign-kanban', [ProductionController::class, 'assignKanbanToPartScans'])
            ->name('production.assign-kanban');

        Route::get('/direct', [ProductionController::class, 'direct'])
            ->middleware(['dea.token'])
            ->name('production.direct.index');
    });

    // injection
    Route::get('/injection', [InjectionController::class, 'index'])->name('injection.index');

    Route::prefix('injection')->group(function () {
        Route::get('/prdreport', [InjectionController::class, 'indexprdreport'])
            ->name('injection.indexprdreport');

        // Scan page / direct page jika nanti dibutuhkan
        Route::get('/direct', [InjectionController::class, 'direct'])
            ->name('injection.direct.index');

        // Cek master mold dari tag mold
        Route::get('/mold-check/{moldCode}', [InjectionController::class, 'moldCheck'])
            ->name('injection.mold-check');

        // Ambil internal part dari customer part / part number barcode
        Route::get('/api-get-internal/{custPart}', [InjectionController::class, 'getInternalPart'])
            ->name('injection.api-get-internal');

        // Simpan hasil scan part anggota mold
        Route::post('/store', [InjectionController::class, 'store'])
            ->name('injection.store');

        // Stop / release injection
        Route::post('/api-stop', [InjectionController::class, 'inboundStop'])
            ->name('injection.api-stop');

        // Ambil list problem berdasarkan line dan kategori
        Route::get('/api-list-stop/{line}/{category}', [InjectionController::class, 'getListStop'])
            ->name('injection.api-list-stop');

        // Insert problem / setup / QC cek
        Route::post('/api-insert-stop', [InjectionController::class, 'insertStop'])
            ->name('injection.api-insert-stop');
    });

    // Quality QC Gate R1
    Route::prefix('quality')->name('quality.')->group(function () {
        Route::get('/', [QualityController::class, 'index'])->name('index');
        Route::get('/scan', [QualityController::class, 'scan'])->name('scan');
        Route::post('/lookup-kanban', [QualityController::class, 'lookupKanban'])->name('lookupKanban');
        Route::post('/inspection', [QualityController::class, 'storeInspection'])->name('storeInspection');
        Route::get('/history', [QualityController::class, 'history'])->name('history');
        Route::get('/history/export/excel', [QualityController::class, 'exportHistory'])->name('history.export');
        Route::get('/history/{inspection}/export', [QualityController::class, 'exportInspection'])->name('show.export');
        Route::get('/history/{inspection}', [QualityController::class, 'show'])->name('show');
        Route::get('/pareto', [QualityController::class, 'pareto'])->name('pareto');
        Route::post('/approval/{inspection}', [QualityController::class, 'approval'])->name('approval');
    });

    //Validation

    Route::get('/validation', [ValidationController::class, 'index'])->name('validation.index');
    Route::prefix('validation')->group(function () {
        Route::get('/kanban/pairing', [ValidationController::class, 'pair'])->name('validation.pairing');
        Route::get('/kanban/pairing/assembly-status', [ValidationController::class, 'assemblyStatus'])
            ->name('validation.pairing.assemblyStatus');
        Route::post('/kanban/pairing/complete', [ValidationController::class, 'completePairing'])
            ->name('validation.pairing.complete');
    });

    // pulling
    Route::prefix('pulling')->group(function () {
        Route::get('/', [PullingController::class, 'index'])
            ->middleware(['dea.token'])
            ->name('pulling.index');

        Route::get('/settings', [PullingController::class, 'settingIndex'])
            ->name('pulling.settings');

        Route::post('/settings/update', [PullingController::class, 'settingUpdate'])
            ->middleware('can:manage-master')
            ->name('pulling.settings.update');

        Route::post('/settings/reorder/add', [PullingController::class, 'addManualItem'])
            ->middleware('can:manage-master')
            ->name('pulling.reorder.add');

        Route::post('/settings/reorder', [PullingController::class, 'reorderByDeliveryTime'])
            ->middleware('can:manage-master')
            ->name('pulling.reorder');

        Route::delete('/settings/reorder/{id}', [PullingController::class, 'deleteItem'])
            ->middleware('can:manage-master')
            ->name('pulling.reorder.delete');

        Route::get('/settings/reorder/options', [PullingController::class, 'addItemOptions'])
            ->name('pulling.reorder.options');

        Route::get('/customer-check/{customer}/{pds?}', [PullingController::class, 'customerCheck'])->name('pulling.customer-check');
        // Route::get('/internal-check/{internal}', [PullingController::class, 'internalCheck'])->name('pulling.internal-check');
        Route::get('/internal-check/{internal}/{isinternal?}', [PullingController::class, 'internalCheck'])->name('pulling.internal-check');
        Route::get('/store', [PullingController::class, 'store'])->name('pulling.store');
        Route::get('/post', [PullingController::class, 'post'])->name('pulling.post');
        Route::get('/mutation', [PullingController::class, 'mutation'])->name('pulling.mutation');
        Route::get('/scan-history', [PullingController::class, 'scanHistory'])->name('pulling.scan-history');

        Route::get('/alamat-chute', [PullingController::class, 'getAlamatChute'])
            ->name('pulling.alamat-chute');

        Route::get('/manual', [PullingController::class, 'manual'])->name('pulling.manual')->middleware('can:manage-master');
        Route::post('/manual', [PullingController::class, 'manualReset'])->name('pulling.manualReset')->middleware('can:manage-master');
        Route::get('/master-alamat-chute', [PullingController::class, 'masterAlamatChuteIndex'])
            ->name('pulling.master-alamat-chute');
        Route::get('/master-alamat-chute/template', [PullingController::class, 'downloadMasterAlamatChuteTemplate'])
            ->name('pulling.master-alamat-chute.template');
        Route::post('/master-alamat-chute/import', [PullingController::class, 'importMasterAlamatChute'])
            ->middleware('can:manage-master')
            ->name('pulling.master-alamat-chute.import');
        Route::post('/master-alamat-chute/clear-all', [PullingController::class, 'clearAllMasterAlamatChute'])
            ->middleware('can:manage-master')
            ->name('pulling.master-alamat-chute.clear-all');
        Route::post('/master-alamat-chute', [PullingController::class, 'storeMasterAlamatChute'])
            ->middleware('can:manage-master')
            ->name('pulling.master-alamat-chute.store');
        Route::put('/master-alamat-chute/{master_alamat_chute}', [PullingController::class, 'updateMasterAlamatChute'])
            ->middleware('can:manage-master')
            ->name('pulling.master-alamat-chute.update');
        Route::delete('/master-alamat-chute/{master_alamat_chute}', [PullingController::class, 'deleteMasterAlamatChute'])
            ->middleware('can:manage-master')
            ->name('pulling.master-alamat-chute.delete');
    });

    // get manifest
    // Route::get('/manifest/{pdsNumber}', [ManifestController::class, 'show'])->name('manifest.show');

    // error log
    Route::prefix('error')->group(function () {
        Route::match(['get', 'post'], '/store', [ErrorLogController::class, 'store'])->name('error.store');
        Route::get('/log', [ErrorLogController::class, 'index'])->name('error.log');
        Route::get('/getErrorLogs', [ErrorLogController::class, 'getErrorLogs'])->name('error.getErrorLogs');
        Route::get('/export', [ErrorLogController::class, 'export'])->name('error.export');
    });

    Route::get('/test', [ProductionController::class, 'test'])->name('test');

    // PIS Dashboard Routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/viewDashboardMutation', [PisController::class, 'viewDashboardMutation'])->name('dashboard.viewDashboardMutation');
        Route::get('/getAjaxMutation', [PisController::class, 'getAjaxMutation'])->name('dashboard.getAjaxMutation');
    });
});
