<?php
namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::withCount('barang');

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('nama_karyawan', 'like', '%' . $request->search . '%')
                  ->orWhere('nip', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('divisi') && $request->divisi != '') {
            $query->where('divisi', $request->divisi);
        }

        if ($request->has('sort')) {
            if ($request->sort == 'nama_asc') {
                $query->orderBy('nama_karyawan', 'asc');
            } elseif ($request->sort == 'nama_desc') {
                $query->orderBy('nama_karyawan', 'desc');
            } else {
                $query->latest('id_karyawan');
            }
        } else {
            $query->latest('id_karyawan');
        }

        $karyawan = $query->paginate(10);
        return view('karyawan.index', compact('karyawan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip'           => 'required|string|unique:m_karyawan,nip',
            'nama_karyawan' => 'required|string|max:255',               
            'divisi'        => 'required|string',
            'jabatan'       => 'required|string',
            'kontak'        => 'required|string',
        ]);

        Karyawan::create($validated);

        return redirect()->route('karyawan.index')->with('success', 'Data Karyawan berhasil ditambahkan');
    }
        
    public function show($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return response()->json($karyawan->load('barang.latestKondisi'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $validated = $request->validate([
            'nip' => [
                'required',
                'string',
                Rule::unique('m_karyawan', 'nip')->ignore($karyawan->id_karyawan, 'id_karyawan'),
            ],
            'nama_karyawan' => 'required|string|max:255',
            'divisi'        => 'required|string',
            'jabatan'       => 'required|string',
            'kontak'        => 'required|string',
        ]);

        $karyawan->update($validated);
        
        return redirect()->route('karyawan.index')->with('success', 'Data Karyawan berhasil diperbarui');
    }

    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();
        
        return redirect()->route('karyawan.index')->with('success', 'Data Karyawan berhasil dihapus.');
    }
}