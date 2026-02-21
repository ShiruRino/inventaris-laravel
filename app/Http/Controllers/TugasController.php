<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Tugas;
use App\Models\User;
use App\Traits\LogAktivitasTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasController extends Controller
{
    use LogAktivitasTrait;

    public function index(Request $request)
    {
        $query = Tugas::with(['admin.karyawan', 'petugas.karyawan', 'barang']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('id_user_petugas') && $request->id_user_petugas != '') {
            $query->where('id_user_petugas', $request->id_user_petugas);
        }

        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('jadwal_mulai', '<=', $request->tanggal)
                  ->whereDate('jadwal_tenggat', '>=', $request->tanggal);
        }

        $tugas = $query->latest('jadwal_mulai')->paginate(10);
        
        $petugas = User::where('role', 'lapangan')->with('karyawan')->get();
        $barang = Barang::select('id_barang', 'kode_barcode', 'nama_barang')->get();

        return view('tugas.index', compact('tugas', 'petugas', 'barang'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user_petugas' => 'required|exists:m_user,id_user',
            'id_barang'       => 'nullable|exists:m_barang,id_barang',
            'jenis_tugas'     => 'required|string|max:255',
            'deskripsi_tugas' => 'nullable|string',
            'jadwal_mulai'    => 'required|date',
            'jadwal_tenggat'  => 'required|date|after_or_equal:jadwal_mulai',
        ]);

        $validated['id_user_admin'] = Auth::id();
        $validated['status'] = 'Belum Dibaca';

        $tugas = Tugas::create($validated);

        $this->catatLog(
            'Tugas', 
            'Create', 
            'Memberikan tugas baru (' . $tugas->jenis_tugas . ') kepada ID Petugas: ' . $tugas->id_user_petugas
        );

        return redirect()->route('tugas.index')->with('success', 'Tugas baru berhasil diberikan kepada tim lapangan.');
    }

    public function show($id)
    {
        $tugas = Tugas::with(['admin.karyawan', 'petugas.karyawan', 'barang'])->findOrFail($id);
        return response()->json($tugas);
    }

    public function update(Request $request, $id)
    {
        $tugas = Tugas::findOrFail($id);

        $validated = $request->validate([
            'id_user_petugas' => 'required|exists:m_user,id_user',
            'id_barang'       => 'nullable|exists:m_barang,id_barang',
            'jenis_tugas'     => 'required|string|max:255',
            'deskripsi_tugas' => 'nullable|string',
            'jadwal_mulai'    => 'required|date',
            'jadwal_tenggat'  => 'required|date|after_or_equal:jadwal_mulai',
            'status'          => 'required|in:Belum Dibaca,Sudah Dibaca,Proses,Selesai'
        ]);

        $tugas->update($validated);

        $this->catatLog(
            'Tugas', 
            'Update', 
            'Memperbarui data tugas (ID: ' . $id . ')'
        );

        return redirect()->route('tugas.index')->with('success', 'Detail tugas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tugas = Tugas::findOrFail($id);
        $tugas->delete();

        $this->catatLog(
            'Tugas', 
            'Delete', 
            'Menghapus data tugas (ID: ' . $id . ')'
        );

        return redirect()->route('tugas.index')->with('success', 'Data tugas berhasil dihapus.');
    }
}