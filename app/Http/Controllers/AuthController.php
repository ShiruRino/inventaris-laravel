<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request){
        $user = User::where('username', $request->username)->first();
        if(!$user || !Hash::check($request->password, $user->password)){
            return back()->with('error', 'Kesalahan dalam username atau password.');
        }
        Auth::login($user);
        return redirect()->route('index')->with('success', 'Login berhasil');
    }
}
