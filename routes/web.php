<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetRequestController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Api\AssetDetailController;
use App\Http\Controllers\IntangibleAssetController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Public asset detail (QR Code scanning)
Route::get('/detail', [AssetDetailController::class, 'show'])->name('asset.detail');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/stats', [DashboardController::class, 'getStats'])->name('api.stats');

    // Assets Management
    Route::middleware(['level:assets-inv'])->group(function () {
        Route::get('/assets-inv', [AssetController::class, 'index'])->name('assets-inv.index');
        Route::get('/assets-inv/create', [AssetController::class, 'create'])->name('assets-inv.create');
        Route::get('/assets-inv/group-detail', [AssetController::class, 'groupDetail'])->name('assets-inv.group-detail');
        Route::post('/assets-inv', [AssetController::class, 'store'])->name('assets-inv.store');
        Route::get('/assets-inv/{asset}', [AssetController::class, 'show'])->name('assets-inv.show');
        Route::get('/assets-inv/{asset}/edit', [AssetController::class, 'edit'])->name('assets-inv.edit');
        Route::put('/assets-inv/{asset}', [AssetController::class, 'update'])->name('assets-inv.update');
        Route::delete('/assets-inv/{asset}', [AssetController::class, 'destroy'])->name('assets-inv.destroy');

        Route::get('/intangible-assets', [IntangibleAssetController::class, 'index'])->name('intangible-assets.index');
        Route::get('/intangible-assets/create', [IntangibleAssetController::class, 'create'])->name('intangible-assets.create');
        Route::post('/intangible-assets', [IntangibleAssetController::class, 'store'])->name('intangible-assets.store');
        Route::get('/intangible-assets/{intangibleAsset}', [IntangibleAssetController::class, 'show'])->name('intangible-assets.show');

        // API for modal
        Route::get('/api/assets/{asset}/detail', [AssetController::class, 'showModal'])->name('api.assets.detail');

        Route::post('/assets/generate-qrcode', [AssetController::class, 'generateQrCode'])->name('assets.generate-qrcode');
        Route::post('/assets/bulk-delete', [AssetController::class, 'bulkDelete'])->name('assets.bulk-delete');
    });

    // Borrowing Management
    Route::middleware(['level:borrowing'])->group(function () {
        Route::resource('borrowings', BorrowingController::class)->except(['edit', 'update']);
        Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnAsset'])->name('borrowings.return');
        Route::post('/borrowings/{borrowing}/approve-cross-unit', [BorrowingController::class, 'approveCrossUnit'])->name('borrowings.approve-cross-unit');
        Route::post('/borrowings/{borrowing}/reject-cross-unit', [BorrowingController::class, 'rejectCrossUnit'])->name('borrowings.reject-cross-unit');
    });

    // Maintenance Management
    Route::middleware(['level:maintenance'])->group(function () {
        Route::resource('maintenances', MaintenanceController::class)->except(['edit']); // update diaktifkan lagi
        Route::get('/maintenances/asset/{id}', [MaintenanceController::class, 'showAssetDetail'])->name('maintenances.asset.detail');
    });

    // User Management
    Route::middleware(['level:users'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Asset Requests Management
    Route::middleware(['level:requests'])->group(function () {
        Route::resource('requests', AssetRequestController::class)
            ->parameters(['requests' => 'assetRequest'])
            ->except(['edit', 'update']);
        Route::post('/requests/{assetRequest}/verify', [AssetRequestController::class, 'verify'])->name('requests.verify');
        Route::post('/requests/{assetRequest}/approve', [AssetRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{assetRequest}/reject', [AssetRequestController::class, 'reject'])->name('requests.reject');
        Route::get('/requests/{assetRequest}/receive', [AssetRequestController::class, 'showReceiveForm'])->name('requests.receive.form');
        Route::post('/requests/{assetRequest}/receive', [AssetRequestController::class, 'receive'])->name('requests.receive');
        Route::post('/requests/{assetRequest}/confirm', [AssetRequestController::class, 'confirmPhysical'])->name('requests.confirm');
        Route::post('/requests/{assetRequest}/disburse', [AssetRequestController::class, 'disburseFund'])->name('requests.disburse');
        Route::get('/requests/{assetRequest}/approval', [AssetRequestController::class, 'approval'])->name('requests.approval');
        Route::post('/requests/{assetRequest}/items/{item}/approve', [AssetRequestController::class, 'approveItem'])->name('requests.approve-item');
    });

    // Reports
    Route::middleware(['level:reports'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    });

    // QR Code Management
    Route::middleware(['level:qrCode'])->group(function () {
        Route::get('/qrcodes', [QrCodeController::class, 'index'])->name('qrcodes.index');
        Route::get('/qrcodes/export/pdf', [QrCodeController::class, 'exportAllPdf'])->name('qrcodes.export-pdf');
        Route::get('/qrcodes/{qrCode}', [QrCodeController::class, 'show'])->name('qrcodes.show');
        Route::delete('/qrcodes/{qrCode}', [QrCodeController::class, 'destroy'])->name('qrcodes.destroy');
        Route::post('/qrcodes/{qrCode}/toggle-status', [QrCodeController::class, 'toggleStatus'])->name('qrcodes.toggle-status');
        Route::get('/qrcodes/{qrCode}/print', [QrCodeController::class, 'print'])->name('qrcodes.print');
    });
});
