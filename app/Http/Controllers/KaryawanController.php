<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $karyawan = Karyawan::orderBy('nama_karyawan')->paginate(10);
        return view('karyawan.index',compact('karyawan'));
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
            'nip' => 'required',
            'nama_karyawan' => 'required|unique:m_karyawan,nip',
            'divisi' => 'required',
            'jabatan' => 'required',
            'kontak' => 'required',
        ];
        $validatedData = Validator::make($request->all(), $rules);
        if($validatedData->fails()){
            return back()->withErrors($validatedData)->withInput();
        }
        Karyawan::create($request->all());
        return redirect()->route('karyawan.index')->with('success', 'Data Karyawan berhasil ditambahkan');
        }
        
        /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $employee = Karyawan::with('barang')->findOrFail($id);
        return response()->json($employee);
    }

    /**
     * Show the form for editing the specified resource.
    */
    public function edit(Karyawan $karyawan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Karyawan $karyawan)
    {
        // dd($request->all());
        // $rules = [
        //     'nip' => 'required|unique:m_karyawan,nip,'.$karyawan->nip . ',id_karyawan',
        //     'nama_karyawan' => 'required',
        //     'divisi' => 'required',
        //     'jabatan' => 'required',
        // ];
        // $validatedData = Validator::make($request->all(), $rules);
        // if($validatedData->fails()){
        //     return back()->withErrors($validatedData)->withInput();
        // }
        $karyawan->update([
            'nip' => $request->nip,
            'nama_karyawan' => $request->nama_karyawan,
            'divisi' => $request->divisi,
            'jabatan' => $request->jabatan,
            'kontak' => $request->kontak,
        ]);
        $karyawan->save();
        
        return redirect()->route('karyawan.index')->with('success', 'Data Karyawan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();
        return redirect()->route('karyawan.index')->with('success', 'Data Karyawan berhasil dihapus.');
    }
}
