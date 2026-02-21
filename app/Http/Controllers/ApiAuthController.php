<?php
namespace App\Http\Controllers;

use App\Models\LogLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Username atau password salah.'
            ], 401);
        }

        $token = $user->createToken('mobile_token')->plainTextToken;

        LogLogin::create([
            'id_user'     => $user->id_user,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'waktu_login' => now(),
            'status_sesi' => 'Aktif',
        ]);

        $user->update([
            'last_login_at'    => now(),
            'last_activity_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Login berhasil.',
            'data'    => [
                'user'  => $user->load('karyawan'),
                'token' => $token
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        LogLogin::where('id_user', $user->id_user)
                ->where('status_sesi', 'Aktif')
                ->update([
                    'waktu_logout' => now(),
                    'status_sesi'  => 'Selesai'
                ]);

        $user->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil.'
        ], 200);
    }
}