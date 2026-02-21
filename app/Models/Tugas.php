<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 't_tugas';
    protected $primaryKey = 'id_tugas';

    protected $fillable = [
        'id_user_admin',
        'id_user_petugas',
        'id_barang',
        'jenis_tugas',
        'deskripsi_tugas',
        'jadwal_mulai',
        'jadwal_tenggat',
        'status',
        'foto_bukti_tugas',
        'catatan_petugas',
    ];

    protected $casts = [
        'jadwal_mulai'     => 'datetime',
        'jadwal_tenggat'   => 'datetime',
        'foto_bukti_tugas' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_user_admin', 'id_user');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'id_user_petugas', 'id_user');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}