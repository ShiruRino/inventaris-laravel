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
        'foto_barang',
    ];
    public function kontrak(){
        return $this->belongsTo(Kontrak::class, 'id_kontrak');
    }
    public function mobilisasi(){
        return $this->hasMany(Mobilisasi::class, 'id_barang');
    }
    public function kondisi(){
        return $this->hasMany(Kondisi::class, 'id_barang')->latest();
    }
    public function latestKondisi(){
        return $this->hasOne(Kondisi::class, 'id_barang')->latestOfMany('id_kondisi');
    }
    public function karyawan(){
        return $this->belongsTo(Karyawan::class, 'id_karyawan_pemegang');
    }
}
