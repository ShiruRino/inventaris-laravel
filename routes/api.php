<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiBarangController;
use App\Http\Controllers\ApiKondisiController;
use App\Http\Controllers\ApiKontrakController;
use App\Http\Controllers\ApiMobilisasiController;
use App\Http\Controllers\ApiTugasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [ApiAuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return response()->json($request->user()->load('karyawan'));
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'role:lapangan'])->group(function () {
    
    Route::post('logout', [ApiAuthController::class, 'logout']);
    
    Route::name('api.')->group(function () {
        
        Route::get('kontrak', [ApiKontrakController::class, 'index']);
        
        Route::get('barang', [ApiBarangController::class, 'index']);
        Route::post('barang', [ApiBarangController::class, 'store']);
        Route::get('barang/{kodeBarcode}', [ApiBarangController::class, 'show']);
        Route::post('barang/{id}/update', [ApiBarangController::class, 'update']);
        Route::delete('barang/{id}', [ApiBarangController::class, 'destroy']);
        
        Route::get('barang/{id}/kondisi', [ApiKondisiController::class, 'index']);
        Route::post('barang/{id}/kondisi', [ApiKondisiController::class, 'store']);
        
        
        Route::get('kondisi/{id}', [ApiKondisiController::class, 'show']);
        Route::post('kondisi/{id}/update', [ApiKondisiController::class, 'update']);
        Route::delete('kondisi/{id}', [ApiKondisiController::class, 'destroy']);
        
        Route::get('mobilisasi/pending', [ApiMobilisasiController::class, 'pending']);
        Route::post('mobilisasi', [ApiMobilisasiController::class, 'store']);
        Route::get('mobilisasi/detail/{id}', [ApiMobilisasiController::class, 'show']);
        Route::delete('mobilisasi/{id}', [ApiMobilisasiController::class, 'destroy']);

        Route::get('tugas', [ApiTugasController::class, 'index']);
        Route::get('tugas/{id}', [ApiTugasController::class, 'show']);
        Route::put('tugas/{id}/status', [ApiTugasController::class, 'updateStatus']);
        Route::post('tugas/{id}/complete', [ApiTugasController::class, 'complete']);
    });
});