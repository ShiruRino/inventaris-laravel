<?php
namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $query = LogAktivitas::with('user.karyawan');

        if ($request->has('search') && $request->search != '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%')
                  ->orWhereHas('karyawan', function($k) use ($request) {
                      $k->where('nama_karyawan', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->has('modul') && $request->modul != '') {
            $query->where('modul', $request->modul);
        }

        if ($request->has('aksi') && $request->aksi != '') {
            $query->where('aksi', $request->aksi);
        }

        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->latest('created_at')->paginate(15);
        $modulList = LogAktivitas::select('modul')->distinct()->pluck('modul');

        return view('log_aktivitas.index', compact('logs', 'modulList'));
    }
}