<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Mobilisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApiMobilisasiController extends Controller
{
    public function index(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        $perPage = $request->input('per_page', 10);
        $mobilisasi = $barang->mobilisasi()->latest()->paginate($perPage);
        
        return response()->json([
            'barang'     => $barang,
            'mobilisasi' => $mobilisasi
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barcode'       => 'required|exists:m_barang,kode_barcode',
            'nip_penerima'       => 'nullable|exists:m_karyawan,nip',
            'lokasi_tujuan'      => 'nullable|string',
            'bukti_serah_terima' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $barang = Barang::where('kode_barcode', $validated['kode_barcode'])->firstOrFail();

        if (!empty($validated['nip_penerima'])) {
            $karyawanPenerima = Karyawan::where('nip', $validated['nip_penerima'])->firstOrFail();
            
            if ($barang->id_karyawan_pemegang === $karyawanPenerima->id_karyawan) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Barang sudah berada di penguasaan karyawan tersebut.'
                ], 422);
            }
        } elseif (!empty($validated['lokasi_tujuan'])) {
            if ($barang->lokasi_fisik === $validated['lokasi_tujuan']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Barang sudah berada di lokasi tersebut.'
                ], 422);
            }
        }

        $asal = $barang->id_karyawan_pemegang !== null 
        ? ($barang->karyawan?->nama_karyawan ?? $barang->karyawan?->nip) 
        : ($barang->lokasi_fisik ?? '-');

        $buktiPath = null;
        if ($request->hasFile('bukti_serah_terima')) {
            $buktiPath = $request->file('bukti_serah_terima')->store('bukti_mobilisasi', 'public');
        }

        $mobilisasi = DB::transaction(function () use ($request, $validated, $barang, $asal, $buktiPath) {
            
            if (!empty($validated['nip_penerima'])) {
                $karyawan = Karyawan::where('nip', $validated['nip_penerima'])->firstOrFail();
                
                $mob = Mobilisasi::create([
                    'id_barang'          => $barang->id_barang,
                    'asal'               => $asal,
                    'id_penerima'        => $karyawan->id_karyawan,
                    'id_user_operator'   => $request->user()->id_user,
                    'bukti_serah_terima' => $buktiPath,
                ]);

                $barang->update([
                    'lokasi_fisik'         => null,
                    'id_karyawan_pemegang' => $karyawan->id_karyawan,
                ]);

                return $mob;
            } else {
                $mob = Mobilisasi::create([
                    'id_barang'          => $barang->id_barang,
                    'asal'               => $asal,
                    'lokasi_tujuan'      => $validated['lokasi_tujuan'],
                    'id_user_operator'   => $request->user()->id_user,
                    'bukti_serah_terima' => $buktiPath,
                ]);

                $barang->update([
                    'id_karyawan_pemegang' => null,
                    'lokasi_fisik'         => $validated['lokasi_tujuan'],
                ]);

                return $mob;
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Mobilisasi berhasil ditambah.',
            'data'    => $mobilisasi
        ], 201);
    }

    public function show(string $id)
    {
        $mobilisasi = Mobilisasi::with('barang')->findOrFail($id);
        
        return response()->json($mobilisasi, 200);
    }

    public function destroy(string $id)
    {
        $mobilisasi = Mobilisasi::findOrFail($id);
        
        if ($mobilisasi->bukti_serah_terima) {
            Storage::disk('public')->delete($mobilisasi->bukti_serah_terima);
        }

        $mobilisasi->delete();
        
        return response()->json([
            'status'  => 'success',
            'message' => 'Data Mobilisasi berhasil dihapus.'
        ], 200);
    }
}