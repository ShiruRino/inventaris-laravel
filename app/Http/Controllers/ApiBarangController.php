<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Kondisi;
use App\Models\Mobilisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApiBarangController extends Controller
{
    public function index(Request $request)
    {
        $totalAset = Barang::count();

        $rusak = Barang::whereHas('latestKondisi', function ($query) {
            $query->whereIn('status_kondisi', ['Rusak Ringan', 'Rusak Berat']);
        })->count();

        $perPage = $request->input('per_page', 10);
        
        $barang = Barang::with(['karyawan', 'latestKondisi'])->latest()->paginate($perPage);
        
        return response()->json([
            'statistik' => [
                'total_aset'  => $totalAset,
                'total_rusak' => $rusak,
            ],
            'barang' => $barang
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_kontrak'        => 'required|exists:m_kontrak,id_kontrak',
            'kode_barcode'      => 'required|unique:m_barang,kode_barcode',
            'nama_barang'       => 'required|string',
            'spesifikasi'       => 'required|string',
            'jumlah_barang'     => 'required|numeric|min:1',
            'status_penguasaan' => 'required|in:personal,lokasi',
            'lokasi_fisik'      => 'required_if:status_penguasaan,lokasi|nullable|string',
            'nip'               => 'required_if:status_penguasaan,personal|nullable|exists:m_karyawan,nip',
            'foto_barang'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('foto_barang')) {
            $imagePath = $request->file('foto_barang')->store('barang', 'public');
        }

        $barang = DB::transaction(function () use ($request, $validatedData, $imagePath) {
            
            $idKaryawanPemegang = null;
            if ($validatedData['status_penguasaan'] == 'personal' && !empty($validatedData['nip'])) {
                $karyawan = Karyawan::where('nip', $validatedData['nip'])->first();
                $idKaryawanPemegang = $karyawan->id_karyawan;
            }

            $barangData = [
                'id_kontrak'    => $validatedData['id_kontrak'],
                'kode_barcode'  => $validatedData['kode_barcode'],
                'nama_barang'   => $validatedData['nama_barang'],
                'spesifikasi'   => $validatedData['spesifikasi'],
                'jumlah_barang' => $validatedData['jumlah_barang'],
                'foto_barang'   => $imagePath,
            ];

            if ($validatedData['status_penguasaan'] == 'personal') {
                $barangData['id_karyawan_pemegang'] = $idKaryawanPemegang;
                $barangData['lokasi_fisik'] = null;
            } else {
                $barangData['lokasi_fisik'] = $validatedData['lokasi_fisik'];
                $barangData['id_karyawan_pemegang'] = null;
            }

            $barang = Barang::create($barangData);

            Mobilisasi::create([
                'id_barang'        => $barang->id_barang,
                'asal'             => '(Vendor)',
                'id_penerima'      => $idKaryawanPemegang,
                'lokasi_tujuan'    => $validatedData['status_penguasaan'] == 'lokasi' ? $validatedData['lokasi_fisik'] : null,
                'id_user_operator' => $request->user()->id_user, 
            ]);

            Kondisi::create([
                'id_barang'        => $barang->id_barang,
                'id_user_operator' => $request->user()->id_user,
                'status_kondisi'   => 'Baik',
                'catatan'          => 'Registrasi Awal',
            ]);

            return $barang;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Barang berhasil ditambah.',
            'data'    => $barang
        ], 201);
    }

    public function show(string $kodeBarcode)
    {
        $barang = Barang::where('kode_barcode', $kodeBarcode)->firstOrFail();
        $barang->load(['kontrak', 'karyawan', 'latestKondisi', 'kondisi', 'mobilisasi']);
        return response()->json($barang, 200);
    }

    public function update(Request $request, string $id)
    {
        $barang = Barang::findOrFail($id);

        $validatedData = $request->validate([
            'id_kontrak'           => 'required|exists:m_kontrak,id_kontrak',
            'nama_barang'          => 'required|string',
            'spesifikasi'          => 'required|string',
            'jumlah_barang'        => 'required|numeric|min:1',
            'status_penguasaan'    => 'required|in:personal,lokasi',
            'lokasi_fisik'         => 'required_if:status_penguasaan,lokasi|nullable|string',
            'nip'                  => 'required_if:status_penguasaan,personal|nullable|exists:m_karyawan,nip',
            'kondisi'              => 'nullable|string',
            'catatan'              => 'nullable|string',
            'foto_barang'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = $barang->foto_barang;
        if ($request->hasFile('foto_barang')) {
            if ($barang->foto_barang) {
                Storage::disk('public')->delete($barang->foto_barang);
            }
            $imagePath = $request->file('foto_barang')->store('barang', 'public');
        }

        $barang = DB::transaction(function () use ($request, $barang, $validatedData, $imagePath) {
            $oldKaryawanId = $barang->getOriginal('id_karyawan_pemegang');
            $oldLokasiFisik = $barang->getOriginal('lokasi_fisik');
            $oldAsal = $oldKaryawanId ? ($barang->karyawan?->nama_karyawan ?? $oldKaryawanId) : $oldLokasiFisik;

            $idKaryawanPemegang = null;
            if ($validatedData['status_penguasaan'] == 'personal' && !empty($validatedData['nip'])) {
                $karyawan = Karyawan::where('nip', $validatedData['nip'])->first();
                $idKaryawanPemegang = $karyawan->id_karyawan;
            }

            $barang->fill([
                'id_kontrak'    => $validatedData['id_kontrak'],
                'nama_barang'   => $validatedData['nama_barang'],
                'jumlah_barang' => $validatedData['jumlah_barang'],
                'spesifikasi'   => $validatedData['spesifikasi'],
                'foto_barang'   => $imagePath,
            ]);

            if ($validatedData['status_penguasaan'] == 'personal') {
                $barang->lokasi_fisik = null;
                $barang->id_karyawan_pemegang = $idKaryawanPemegang;

                if ($barang->isDirty('id_karyawan_pemegang') || $oldLokasiFisik !== null) {
                    Mobilisasi::create([
                        'id_barang'        => $barang->id_barang,
                        'asal'             => $oldAsal,
                        'id_penerima'      => $idKaryawanPemegang,
                        'id_user_operator' => $request->user()->id_user,
                    ]);
                }
            } elseif ($validatedData['status_penguasaan'] == 'lokasi') {
                $barang->id_karyawan_pemegang = null;
                $barang->lokasi_fisik = $validatedData['lokasi_fisik'];

                if ($barang->isDirty('lokasi_fisik') || $oldKaryawanId !== null) {
                    Mobilisasi::create([
                        'id_barang'        => $barang->id_barang,
                        'asal'             => $oldAsal,
                        'lokasi_tujuan'    => $validatedData['lokasi_fisik'],
                        'id_user_operator' => $request->user()->id_user,
                    ]);
                }
            }

            $barang->save();

            if (!empty($validatedData['kondisi'])) {
                Kondisi::create([
                    'id_barang'        => $barang->id_barang,
                    'id_user_operator' => $request->user()->id_user,
                    'status_kondisi'   => $validatedData['kondisi'],
                    'catatan'          => $validatedData['catatan'] ?? null,
                ]);
            }

            return $barang;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Barang berhasil diperbarui.',
            'data'    => $barang
        ], 200);
    }

    public function destroy(string $id)
    {
        $barang = Barang::findOrFail($id);
        
        if ($barang->foto_barang) {
            Storage::disk('public')->delete($barang->foto_barang);
        }

        $barang->delete();
        
        return response()->json([
            'status'  => 'success',
            'message' => 'Data Barang berhasil dihapus.',
        ], 200);
    }
}