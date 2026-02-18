<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kontrak extends Model
{
    protected $table = 'm_kontrak';
    protected $primaryKey = 'id_kontrak';
    protected $fillable = [
        'no_kontrak',
        'tahun_kontrak',
        'nama_vendor',
        'pihak_pengada',
        'keterangan',
        ];
    public function barang(){
        return $this->hasMany(Barang::class, 'id_barang');
    }
}
