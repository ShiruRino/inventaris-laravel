<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobilisasi extends Model
{
    protected $table = 't_mobilisasi';
    protected $primaryKey = 'id_mobilisasi';
    protected $fillable = [
        'id_barang',
        'asal',
        'id_penerima',
        'lokasi_tujuan',
        'id_user_operator',
        'bukti_serah_terima',
    ];
    public function barang(){
        return $this->belongsTo(Barang::class, 'id_barang');
    }
    public function penerima(){
        return $this->belongsTo(Karyawan::class, 'id_penerima');
    }
    public function operator(){
        return $this->belongsTo(User::class, 'id_user_operator');
    }
}
