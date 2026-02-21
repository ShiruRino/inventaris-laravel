<?php

namespace App\Traits;

use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogAktivitasTrait
{
    public function catatLog($modul, $aksi, $keterangan)
    {
        LogAktivitas::create([
            'id_user'    => Auth::id(),
            'modul'      => $modul,
            'aksi'       => $aksi,
            'keterangan' => $keterangan,
            'ip_address' => Request::ip(),
        ]);
    }
}