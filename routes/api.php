<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SampleRequestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('public/sample-request')->group(function () {
    Route::post('/store', [SampleRequestController::class, 'store'])->name('api.public.sample_request.store');
});