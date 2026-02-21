<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Mobilisasi;
use App\Traits\LogAktivitasTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApiMobilisasiController extends Controller
{
    use LogAktivitasTrait;

    public function pending()
    {
        $barangPending = Barang::with('kontrak')
            ->whereNull('id_karyawan_pemegang')
            ->whereNull('lokasi_fisik')
            ->latest('id_barang')
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data barang menunggu serah terima',
            'data'    => $barangPending
        ], 200);
    }

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
            'id_barang'          => 'required|array|min:1',
            'id_barang.*'        => 'exists:m_barang,id_barang',
            'jenis_transaksi'    => 'required|in:karyawan,lokasi',
            'nip_penerima'       => 'required_if:jenis_transaksi,karyawan|nullable|exists:m_karyawan,nip',
            'lokasi_tujuan'      => 'required_if:jenis_transaksi,lokasi|nullable|string',
            'bukti_serah_terima' => 'nullable|image|mimes:jpg,png,jpeg,pdf|max:2048',
        ]);

        $idPenerima = null;
        if ($validated['jenis_transaksi'] == 'karyawan' && !empty($validated['nip_penerima'])) {
            $karyawanPenerima = Karyawan::where('nip', $validated['nip_penerima'])->firstOrFail();
            $idPenerima = $karyawanPenerima->id_karyawan;
        }

        $buktiPath = null;
        if ($request->hasFile('bukti_serah_terima')) {
            $buktiPath = $request->file('bukti_serah_terima')->store('bukti_mobilisasi', 'public');
        }

        $mobilisasis = DB::transaction(function () use ($request, $validated, $idPenerima, $buktiPath) {
            
            $barangs = Barang::whereIn('id_barang', $validated['id_barang'])->get();
            $createdMobilisasis = [];

            foreach ($barangs as $barang) {
                if ($validated['jenis_transaksi'] == 'karyawan' && $barang->id_karyawan_pemegang === $idPenerima) {
                    continue;
                } elseif ($validated['jenis_transaksi'] == 'lokasi' && $barang->lokasi_fisik === $validated['lokasi_tujuan']) {
                    continue;
                }

                $asalString = $barang->id_karyawan_pemegang !== null 
                    ? ($barang->karyawan?->nama_karyawan ?? $barang->karyawan?->nip) 
                    : ($barang->lokasi_fisik ?? '(Vendor)');

                if ($validated['jenis_transaksi'] == 'karyawan') {
                    $mob = Mobilisasi::create([
                        'id_barang'          => $barang->id_barang,
                        'asal'               => $asalString,
                        'id_penerima'        => $idPenerima,
                        'id_user_operator'   => $request->user()->id_user,
                        'bukti_serah_terima' => $buktiPath,
                    ]);

                    $barang->update([
                        'lokasi_fisik'         => null,
                        'id_karyawan_pemegang' => $idPenerima,
                    ]);

                    $createdMobilisasis[] = $mob;
                } 
                elseif ($validated['jenis_transaksi'] == 'lokasi') {
                    $mob = Mobilisasi::create([
                        'id_barang'          => $barang->id_barang,
                        'asal'               => $asalString,
                        'lokasi_tujuan'      => $validated['lokasi_tujuan'],
                        'id_user_operator'   => $request->user()->id_user,
                        'bukti_serah_terima' => $buktiPath,
                    ]);

                    $barang->update([
                        'id_karyawan_pemegang' => null,
                        'lokasi_fisik'         => $validated['lokasi_tujuan'],
                    ]);

                    $createdMobilisasis[] = $mob;
                }
            }

            $tujuan = $validated['jenis_transaksi'] == 'karyawan' ? 'Karyawan (NIP: ' . $validated['nip_penerima'] . ')' : 'Lokasi (' . $validated['lokasi_tujuan'] . ')';
            
            $this->catatLog(
                'Mobilisasi', 
                'Create', 
                'Melakukan serah terima ' . count($createdMobilisasis) . ' barang ke ' . $tujuan
            );

            return $createdMobilisasis;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Serah terima untuk barang terpilih berhasil diproses.',
            'data'    => $mobilisasis
        ], 201);
    }

    public function show(string $id)
    {
        $mobilisasi = Mobilisasi::with('barang')->findOrFail($id);
        
        return response()->json($mobilisasi, 200);
    }

    public function destroy(Request $request, string $id)
    {
        $mobilisasi = Mobilisasi::findOrFail($id);
        
        if ($mobilisasi->bukti_serah_terima) {
            Storage::disk('public')->delete($mobilisasi->bukti_serah_terima);
        }

        $mobilisasi->delete();

        $this->catatLog(
            'Mobilisasi', 
            'Delete', 
            'Menghapus data mobilisasi (ID: ' . $id . ')'
        );
        
        return response()->json([
            'status'  => 'success',
            'message' => 'Data Mobilisasi berhasil dihapus.'
        ], 200);
    }
}