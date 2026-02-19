<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Mobilisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MobilisasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mobilisasi = \App\Models\Mobilisasi::latest()->with(['barang', 'penerima', 'operator'])->paginate(10);
        $karyawan = \App\Models\Karyawan::all();
        
        // PASS KONTRAK TO VIEW
        $kontrak = \App\Models\Kontrak::all(); 

        return view('mobilisasi.index', compact('mobilisasi', 'karyawan', 'kontrak'));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            "id_barang"=> "required|exists:m_barang,id_barang",
            "nip_penerima"=> "nullable|exists:m_karyawan,nip",
            "lokasi_tujuan"=> "nullable",
            "bukti_serah_terima"=> "nullable|image|mimes:jpg,png,jpeg|max:2048",
        ];
        $validatedData = Validator::make($request->all(), $rules);
        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        $barang = Barang::findOrFail($request->id_barang);
        $asal = $barang->id_karyawan_pemegang !== null ? $barang->karyawan->nama_karyawan : $barang->lokasi_fisik;
        if($request->nip_penerima) {
            $karyawan = Karyawan::where('nip', $request->nip_penerima)->firstOrFail();
            Mobilisasi::create([
                'id_barang' => $barang->id_barang,
                'asal' => $asal ?? '-',
                'id_penerima' => $karyawan->id_karyawan,
                'id_user_operator' => Auth::user()->id_user
            ]);
            $barang->update([
                'lokasi_fisik'=> null,
                'id_karyawan_pemegang'=> $karyawan->id_karyawan,
            ]);
            $barang->save();
        }
        else{
            Mobilisasi::create([
                'id_barang' => $barang->id_barang,
                'asal' => $asal ?? '-',
                'lokasi_tujuan' => $request->lokasi_tujuan,
                'id_user_operator' => Auth::user()->id_user
            ]);
            $barang->update([
                'id_karyawan_pemegang'=> null,
                'lokasi_fisik'=> $request->lokasi_tujuan,
            ]);
            $barang->save();
        }
        return redirect()->route('mobilisasi.index')->with('success','Data Mobilisasi berhasil ditambah.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Mobilisasi $mobilisasi)
    {
        //
    }
    /**
     * Fetch Karyawan by NIP (AJAX)
     */
    public function getKaryawanByNip($nip)
    {
        $karyawan = Karyawan::where('nip', $nip)->first();

        if ($karyawan) {
            return response()->json([
                'status' => 'success',
                'nama_karyawan' => $karyawan->nama_karyawan,
                'jabatan' => $karyawan->jabatan ?? '-'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'NIP tidak ditemukan'
        ], 404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mobilisasi $mobilisasi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mobilisasi $mobilisasi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mobilisasi $mobilisasi)
    {
        //
    }
}
