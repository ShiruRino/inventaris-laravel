<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use Illuminate\Http\Request;

class ApiKontrakController extends Controller
{
    public function index(Request $request){
        $perPage = $request->input("per_page",10);
        $kontrak = Kontrak::latest()->paginate($perPage);
        return response()->json($kontrak);
    }
}
