<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DistributorLabController;
use App\Http\Controllers\UserRequestController;
use App\Http\Controllers\GuestRequestController;
use App\Services\WhatsappService;
use App\Http\Controllers\ExpeditionController;
use App\Http\Controllers\SampleRequestController; 

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $role = Auth::user()->role;

    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($role === 'distributor') {
        return redirect()->route('distributor.dashboard');
    }
    
    return view('dashboard'); // Tampilan dashboard untuk User biasa
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/request/create', [AdminController::class, 'createRequest'])->name('admin.create_request');
    Route::post('/request/store', [AdminController::class, 'storeRequest'])->name('admin.request.store');
    Route::get('request/{id}',[AdminController::class,'showRequest'])->name('admin.request.show');
    Route::get('/verify/{id}/{status}', [AdminController::class, 'verifyPayment'])->name('admin.verify');
    Route::get('/request/{id}/qr_code', [AdminController::class, 'generateQrCode'])->name('admin.qr_code');
    Route::get('/request/{id}/print-qr', [AdminController::class, 'printQr'])->name('admin.sample.print_qr');
    Route::get('/get-requests-json', [AdminController::class, 'getLatestRequests'])->name('admin.requests_json');
    Route::get('/status-realtime', [AdminController::class, 'getStatusRealtime'])->name('admin.status_realtime');
    Route::get('/select-lab', [AdminController::class, 'selectLab'])->name('admin.select-lab');
    Route::get('/create', [AdminController::class, 'create'])->name('admin.create');
});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
}); 

// Distributor Routes (Digabung menjadi 1 grup)
Route::middleware(['auth', 'role:distributor'])->prefix('distributor')->group(function () {
    Route::get('/dashboard', [DistributorLabController::class, 'index'])->name('distributor.dashboard');
    Route::get('/sample/{code}', [DistributorLabController::class, 'showByCode'])->name('distributor.sample.show');
    Route::post('/process-scan', [DistributorLabController::class, 'processScan'])->name('distributor.process_scan');
    
    // Route Detail & Update Status (Sesuai panggilan JS)
    Route::get('/samples/{id}/detail', [DistributorLabController::class, 'getSampleDetail'])->name('distributor.samples.detail');
    Route::post('/samples/{id}/update-status', [DistributorLabController::class, 'updateStatus'])->name('distributor.samples.update-status');
});

// User & Guest Request Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/user/request/create', [UserRequestController::class, 'create'])->name('user.create_request');
    Route::post('/user/request/store', [UserRequestController::class, 'store'])->name('user.store_request');
});

// Route simpan pendaftaran online
Route::post('/sample-request/store', [SampleRequestController::class, 'store'])->name('sample-request.store');

Route::get('/pengajuan-online', [GuestRequestController::class, 'create'])->name('guest.create_request');
Route::post('/pengajuan-online', [GuestRequestController::class, 'store'])->name('guest.store_request');


Route::get('/tracking', [GuestRequestController::class, 'showTrackingForm'])->name('tracking.index');

// 2. Menerima Form Submit Pencarian (POST)
Route::post('/tracking', [GuestRequestController::class, 'trackSample'])->name('tracking.search');

// Expedition Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/expedition/provinces', [ExpeditionController::class, 'getProvinces'])->name('expedition.provinces');
    Route::get('/expedition/cities/{provinceId}', [ExpeditionController::class, 'getCities'])->name('expedition.cities');
    Route::post('/expedition/check-cost', [ExpeditionController::class, 'checkCost'])->name('expedition.check_cost');
    Route::post('/expedition/update-tracking/{id}', [ExpeditionController::class, 'updateTracking'])->name('expedition.update_tracking');
});

require __DIR__.'/auth.php';