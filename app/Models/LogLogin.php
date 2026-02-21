<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogLogin extends Model
{
    use HasFactory;

    protected $table = 't_log_login';
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_user',
        'ip_address',
        'user_agent',
        'waktu_login',
        'waktu_logout',
        'status_sesi'
    ];

    protected $casts = [
        'waktu_login'  => 'datetime',
        'waktu_logout' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}