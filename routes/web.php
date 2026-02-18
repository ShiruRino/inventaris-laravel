<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaryawanController;
use App\Models\Barang;
use App\Models\Kondisi;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
})->name('login.index');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::middleware('auth')->group(function(){
    Route::get('dasbor', function(){
        $totalAset = Barang::count();
        $asetPersonal = Barang::whereNotNull('id_karyawan_pemegang')->count();
        $asetNonPersonal = Barang::whereNotNull('lokasi_fisik')->count();
        $asetRuslang = Kondisi::where('status_kondisi', 'Rusak Ringan')->count() + Kondisi::where('status_kondisi', 'Rusak Berat')->count() + Kondisi::where('status_kondisi', 'Hilang')->count();
    
        $asetBaik = Kondisi::where('status_kondisi', 'Baik')->count();
        $asetRingan = Kondisi::where('status_kondisi', 'Rusak Ringan')->count();
        $asetBerat = Kondisi::where('status_kondisi', 'Rusak Berat')->count();
        $asetHilang = Kondisi::where('status_kondisi', 'Hilang')->count();
        return view('index', compact('totalAset','asetPersonal', 'asetNonPersonal', 'asetRuslang', 'asetBaik', 'asetRingan', 'asetBerat', 'asetHilang'));
    })->name('index');
    Route::resource('karyawan', KaryawanController::class);
});