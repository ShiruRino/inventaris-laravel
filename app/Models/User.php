<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'm_user';
    protected $primaryKey = 'id_user';
    protected $fillable = [
        'id_karyawan',
        'username',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function karyawan(){
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }
    public function operator_mobilisasi(){
        return $this->hasMany(Mobilisasi::class, 'id_user_operator');
    }
    public function kondisi(){
        return $this->hasMany(Kondisi::class, 'id_user_operator');
    }
}
