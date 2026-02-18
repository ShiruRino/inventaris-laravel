<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kondisi extends Model
{
    protected $table = 't_kondisi';
    protected $primaryKey = 'id_kondisi';
    protected $fillable = [
        'id_barang',
        'id_user_operator',
        'status_kondisi',
        'catatan',
        'foto_kondisi',
    ];
    public function barang(){
        return $this->belongsTo(Barang::class, 'id_barang');
    }
    public function operator(){
        return $this->belongsTo(User::class, 'id_user_operator');
    }
}
