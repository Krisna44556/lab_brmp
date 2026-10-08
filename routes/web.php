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
use App\Http\Controllers\PublicSampleController;

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
    // 1. Route Halaman Utama Dashboard
    Route::match(['get', 'post'], '/dashboard', [DistributorLabController::class, 'index'])->name('distributor.dashboard');
    
    // 2. Route Scan (POST AJAX)
    Route::post('/process-scan', [DistributorLabController::class, 'processScan'])->name('distributor.process_scan');
    
    // 3. Route Detail & Status Update
    Route::get('/samples/{id}/detail', [App\Http\Controllers\DistributorLabController::class, 'getDetail'])->name('samples.detail');
    Route::post('/samples/{id}/update-status', [DistributorLabController::class, 'updateStatus'])->name('distributor.sample.update_status');
    
    // 4. Route Cari Berdasarkan Kode (Opsional - Beri batasan regex agar tidak bentrok)
    Route::get('/sample/{code}', [DistributorLabController::class, 'showByCode'])->name('distributor.sample.show');
    
});

// User & Guest Request Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/user/request/create', [UserRequestController::class, 'create'])->name('user.create_request');
    Route::post('/user/request/store', [UserRequestController::class, 'store'])->name('user.store_request');
});



//oute pendaftaran via Web tanpa middleware CSRF
Route::post('/api/sample-request/store', [SampleRequestController::class, 'store'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]); 

Route::get('/pendaftaran', [GuestRequestController::class, 'create'])->name('pendaftaran.create');
Route::post('/pendaftaran', [GuestRequestController::class, 'store'])->name('pendaftaran.store');

// Route AJAX Parameter (WAJIB PUBLIK agar bisa diakses JS saat belum login)
Route::get('/get-parameters-by-lab/{labId}', [GuestRequestController::class, 'getParametersByLab']);

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

Route::get('/pendaftaran', function () {
    return view('frontend.pendaftaran');
});

Route::get('/', function () {
    return view('frontend.index');
});

// 2. Route untuk memproses simpan data dari FE
Route::post('/api/sample-request/store', [SampleRequestController::class, 'store'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

    // Route untuk mengambil parameter secara umum/publik
Route::get('/public/parameters', [PublicSampleController::class, 'getParameters']);

// Route untuk menyimpan formulir pengajuan
Route::post('/public/sample-request/store', [PublicSampleController::class, 'store']);

Route::get('/get-parameters-by-lab/{lab}', [PublicSampleController::class, 'getParameters']);



require __DIR__.'/auth.php';