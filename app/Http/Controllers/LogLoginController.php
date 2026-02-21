<?php
namespace App\Http\Controllers;

use App\Models\LogLogin;
use Illuminate\Http\Request;

class LogLoginController extends Controller
{
    public function index(Request $request)
    {
        $query = LogLogin::with('user.karyawan');

        if ($request->has('search') && $request->search != '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%')
                  ->orWhereHas('karyawan', function($k) use ($request) {
                      $k->where('nama_karyawan', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status_sesi', $request->status);
        }

        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('waktu_login', $request->tanggal);
        }

        $logs = $query->latest('waktu_login')->paginate(15);

        return view('log_login.index', compact('logs'));
    }
}