<?php
namespace App\Http\Controllers;

use App\Models\Kontrak;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KontrakController extends Controller
{
    public function index(Request $request)
    {
        $query = Kontrak::withCount('barang');

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('no_kontrak', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_vendor', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('tahun') && $request->tahun != '') {
            $query->where('tahun_kontrak', $request->tahun);
        }

        if ($request->has('sort')) {
            if ($request->sort == 'terlama') {
                $query->oldest('id_kontrak');
            } elseif ($request->sort == 'vendor_asc') {
                $query->orderBy('nama_vendor', 'asc');
            } elseif ($request->sort == 'vendor_desc') {
                $query->orderBy('nama_vendor', 'desc');
            } else {
                $query->latest('id_kontrak');
            }
        } else {
            $query->latest('id_kontrak');
        }

        $kontrak = $query->paginate(10);
        $tahunList = Kontrak::select('tahun_kontrak')->distinct()->orderBy('tahun_kontrak', 'desc')->pluck('tahun_kontrak');
        
        return view("kontrak.index", compact("kontrak", "tahunList"));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_kontrak'    => 'required|string|unique:m_kontrak,no_kontrak',
            'tahun_kontrak' => 'required|integer|digits:4|between:1900,2100',
            'nama_vendor'   => 'required|string',
            'pihak_pengada' => 'required|string',
        ]);

        Kontrak::create($validated);

        return redirect()->route('kontrak.index')->with('success', 'Data Kontrak berhasil ditambahkan.');
    }

    public function show($id)
    {
        $kontrak = Kontrak::findOrFail($id);
        return response()->json($kontrak->load('barang.latestKondisi'));
    }

    public function update(Request $request, Kontrak $kontrak)
    {
        $validated = $request->validate([
            'no_kontrak' => [
                'required',
                'string',
                Rule::unique('m_kontrak', 'no_kontrak')->ignore($kontrak->id_kontrak, 'id_kontrak'),
            ],
            'tahun_kontrak' => 'required|integer|digits:4|between:1900,2100',
            'nama_vendor'   => 'required|string',
            'pihak_pengada' => 'required|string',
        ]);

        $kontrak->update($validated);

        return redirect()->route('kontrak.index')->with('success', 'Data Kontrak berhasil diperbarui');
    }

    public function destroy(Kontrak $kontrak)
    {
        $kontrak->delete();
        
        return redirect()->route('kontrak.index')->with('success','Data Kontrak berhasil dihapus.');
    }
}