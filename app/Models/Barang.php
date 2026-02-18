<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'm_barang';
    protected $primaryKey = 'id_barang';
    protected $fillable = [
        'id_kontrak',
        'kode_barcode',
        'nama_barang',
        'spesifikasi',
        'jumlah_barang',
        'lokasi_fisik',
        'id_karyawan_pemegang',
    ];
    public function kontrak(){
        return $this->belongsTo(Kontrak::class, 'id_kontrak');
    }
    public function mobilisasi(){
        return $this->belongsTo(Mobilisasi::class, 'id_barang');
    }
    public function kondisi(){
        return $this->belongsTo(Kondisi::class, 'id_barang');
    }
    public function karyawan(){
        return $this->belongsTo(Karyawan::class, 'id_karyawan_pemegang');
    }
}
