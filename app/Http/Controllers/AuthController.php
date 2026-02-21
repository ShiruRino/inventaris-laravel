<?php
namespace App\Http\Controllers;

use App\Models\LogLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $log = LogLogin::create([
                'id_user'     => Auth::id(),
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'waktu_login' => now(),
                'status_sesi' => 'Aktif',
            ]);

            $request->session()->put('id_log_login', $log->id_log);
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->update([
                'last_login_at'    => now(),
                'last_activity_at' => now(),
            ]);
            
            return redirect()->route('index')->with('success', 'Login berhasil');
        }

        return back()->with('error', 'Kesalahan dalam username atau password.');
    }

    public function logout(Request $request)
    {
        $idLog = $request->session()->get('id_log_login');
        
        if ($idLog) {
            LogLogin::where('id_log', $idLog)->update([
                'waktu_logout' => now(),
                'status_sesi'  => 'Selesai'
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.index')->with('success', 'Logout berhasil');
    }
}