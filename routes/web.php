<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\MobilisasiController;
use App\Http\Controllers\UserController;
use App\Models\Barang;
use App\Models\Kondisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('login');
})->name('login.index');
Route::get('/', function () {
    return view('login');
})->name('login.index');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::middleware(['auth','role:admin'])->group(function(){
    Route::get('dasbor', function(){
        $totalAset = Barang::sum('jumlah_barang');
        $asetPersonal = Barang::whereNotNull('id_karyawan_pemegang')->sum('jumlah_barang');
        $asetNonPersonal = Barang::whereNotNull('lokasi_fisik')->sum('jumlah_barang');

        $latestKondisiIds = Kondisi::select(DB::raw('MAX(id_kondisi)'))->groupBy('id_barang');

        $kondisiCounts = Kondisi::whereIn('id_kondisi', $latestKondisiIds)
            ->select('status_kondisi', DB::raw('count(*) as total'))
            ->groupBy('status_kondisi')
            ->pluck('total', 'status_kondisi');

        $asetBaik = $kondisiCounts->get('Baik', 0);
        $asetRingan = $kondisiCounts->get('Rusak Ringan', 0);
        $asetBerat = $kondisiCounts->get('Rusak Berat', 0);
        $asetHilang = $kondisiCounts->get('Hilang', 0);

        $asetRuslang = $asetRingan + $asetBerat + $asetHilang;

        $kategoriData = Barang::select('kategori', DB::raw('SUM(jumlah_barang) as total'))
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $labelKategori = $kategoriData->keys();
        $dataKategori = $kategoriData->values();

        return view('index', compact(
            'totalAset', 'asetPersonal', 'asetNonPersonal', 
            'asetRuslang', 'asetBaik', 'asetRingan', 'asetBerat', 'asetHilang',
            'labelKategori', 'dataKategori'
        ));
    })->name('index');
    Route::get('api/karyawan/nip/{nip}', [MobilisasiController::class, 'getKaryawanByNip'])->name('karyawan.bynip');
    Route::get('api/karyawan/id/{id}', [KaryawanController::class,'show']);
    Route::get('api/kontrak/{id}', [KontrakController::class,'show']);
    Route::resource('karyawan', KaryawanController::class);
    Route::resource('kontrak', KontrakController::class);
    Route::get('/barangs/print-all', [BarangController::class, 'printAll'])->name('barang.printAll');
    Route::get('barang/kontrak/{id_kontrak}', [BarangController::class, 'getByKontrak'])
    ->name('barang.by_kontrak');
    Route::resource('barang', BarangController::class);
    Route::get('/barang/{id}/print', [BarangController::class, 'printLabel'])->name('barang.print');
    Route::get('mobilisasi/export', [MobilisasiController::class, 'export'])->name('mobilisasi.export');
    Route::resource('mobilisasi', MobilisasiController::class);
    Route::resource('user', UserController::class);
});