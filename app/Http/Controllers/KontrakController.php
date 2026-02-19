<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KontrakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kontrak =Kontrak::latest()->paginate(10);
        return view("kontrak.index", compact("kontrak"));
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
            "no_kontrak"=> "required|unique:m_kontrak,no_kontrak",
            'tahun_kontrak' => 'required|integer|digits:4|between:1900,2100',
            "nama_vendor"=> "required",
            "pihak_pengada"=> "required",
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        Kontrak::create($request->all());
        return redirect()->route('kontrak.index')->with("success","Data Kontrak berhasil ditambahkan.");
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kontrak =Kontrak::with('barang')->findOrFail($id);
        return response()->json($kontrak);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kontrak $kontrak)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kontrak $kontrak)
    {
        $kontrak->update($request->all());
        $kontrak->save();
        return redirect()->route('kontrak.index')->with('success','Data Kontrak berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kontrak $kontrak)
    {
        $kontrak->delete();
        return redirect()->route('kontrak.index')->with('success','Data Kontrak berhasil dihapus.');
    }
}
