<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'm_user';
    protected $primaryKey = 'id_user';
    protected $fillable = [
        'id_karyawan',
        'username',
        'password',
        'role',
        'last_login_at',
        'last_activity_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['is_online'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_login_at'     => 'datetime',
            'last_activity_at'  => 'datetime',
        ];
    }

    public function getIsOnlineAttribute()
    {
        if ($this->last_activity_at) {
            return $this->last_activity_at->diffInMinutes(now()) < 5;
        }
        return false;
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }

    public function operator()
    {
        return $this->hasMany(Mobilisasi::class, 'id_user_operator');
    }

    public function kondisi()
    {
        return $this->hasMany(Kondisi::class, 'id_user_operator');
    }

    public function tugasDiberikan()
    {
        return $this->hasMany(Tugas::class, 'id_user_admin', 'id_user');
    }

    public function tugasDiterima()
    {
        return $this->hasMany(Tugas::class, 'id_user_petugas', 'id_user');
    }

    public function riwayatLogin()
    {
        return $this->hasMany(LogLogin::class, 'id_user', 'id_user')->latest('waktu_login');
    }

    public function riwayatAktivitas()
    {
        return $this->hasMany(LogAktivitas::class, 'id_user', 'id_user')->latest('created_at');
    }
}