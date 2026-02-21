<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Traits\LogAktivitasTrait;
use Illuminate\Http\Request;

class ApiTugasController extends Controller
{
    use LogAktivitasTrait;

    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Tugas::with(['admin.karyawan', 'barang'])
                      ->where('id_user_petugas', $user->id_user);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('jadwal_mulai', '<=', $request->tanggal)
                  ->whereDate('jadwal_tenggat', '>=', $request->tanggal);
        }

        $perPage = $request->input('per_page', 10);
        $tugas = $query->latest('jadwal_mulai')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $tugas
        ], 200);
    }

    public function show($id)
    {
        $tugas = Tugas::with(['admin.karyawan', 'barang'])->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data'   => $tugas
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Sudah Dibaca,Proses'
        ]);

        $tugas = Tugas::findOrFail($id);
        $tugas->update(['status' => $validated['status']]);

        $this->catatLog(
            'Tugas', 
            'Update Status', 
            'Memperbarui status tugas (ID: ' . $id . ') menjadi ' . $validated['status']
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Status tugas berhasil diperbarui.',
            'data'    => $tugas
        ], 200);
    }

    public function complete(Request $request, $id)
    {
        $tugas = Tugas::findOrFail($id);

        $validated = $request->validate([
            'catatan_petugas'    => 'required|string',
            'foto_bukti_tugas'   => 'nullable|array',
            'foto_bukti_tugas.*' => 'image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $fotoPaths = $tugas->foto_bukti_tugas ?? [];
        
        if ($request->hasFile('foto_bukti_tugas')) {
            foreach ($request->file('foto_bukti_tugas') as $file) {
                $fotoPaths[] = $file->store('bukti_tugas', 'public');
            }
        }

        $tugas->update([
            'catatan_petugas'  => $validated['catatan_petugas'],
            'foto_bukti_tugas' => empty($fotoPaths) ? null : $fotoPaths,
            'status'           => 'Selesai'
        ]);

        $this->catatLog(
            'Tugas', 
            'Complete', 
            'Menyelesaikan tugas (ID: ' . $id . ')'
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Tugas berhasil diselesaikan.',
            'data'    => $tugas
        ], 200);
    }
}