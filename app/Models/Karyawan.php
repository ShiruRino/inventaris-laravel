<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'm_karyawan';
    protected $primaryKey = 'id_karyawan';
    protected $fillable = [
        'nip',
        'nama_karyawan',
        'divisi',
        'jabatan',
    ];
    public function user(){
        return $this->hasOne(User::class, 'id_user');
    }
    public function pemberi_mobilisasi(){
        return $this->hasMany(Mobilisasi::class, 'id_pemberi');
    }
    public function penerima_mobilisasi(){
        return $this->hasMany(Mobilisasi::class, 'id_penerima');
    }
    public function barang(){
        return $this->hasMany(Barang::class, 'id_karyawan_pemegang');
    }
}
