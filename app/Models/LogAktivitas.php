<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 't_log_aktivitas';
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_user',
        'modul',
        'aksi',
        'keterangan',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}