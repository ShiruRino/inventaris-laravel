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
        $employees = Karyawan::orderBy('nama_karyawan');
        return view('karyawan.index',compact('employees'));
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
        ];
        $validatedData = Validator::make($request->all, $rules);
        if($validatedData->fails()){
            return back()->withErrors($validatedData)->withInput();
        }
        Karyawan::create([
            'nip' => $request->nip,
            'nama_karyawan' => $request->nama_karyawan,
            'divisi' => $request->divisi,
            'jabatan' => $request->jabatan,
        ]);
        return redirect()->route('karyawan.index')->with('success', 'Data Karyawan berhasil ditambahkan');
        }
        
        /**
     * Display the specified resource.
     */
    public function show(Karyawan $karyawan)
    {
        //
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
        $rules = [
            'id_karyawan' => 'required',
            'nip' => 'required|unique:m_karyawan,nip,'.$karyawan->nip,
            'nama_karyawan' => 'required',
            'divisi' => 'required',
            'jabatan' => 'required',
        ];
        $validatedData = Validator::make($request->all, $rules);
        if($validatedData->fails()){
            return back()->withErrors($validatedData)->withInput();
        }
        $karyawan->update([
            'nip' => $request->nip,
            'nama_karyawan' => $request->nama_karyawan,
            'divisi' => $request->divisi,
            'jabatan' => $request->jabatan,
        ]);
        
        return redirect()->route('karyawan.index')->with('success', 'Data Karyawan berhasil ditambahkan');
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
