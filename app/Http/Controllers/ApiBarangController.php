<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Kondisi;
use App\Models\Kontrak;
use App\Models\Mobilisasi;
use App\Traits\LogAktivitasTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApiBarangController extends Controller
{
    use LogAktivitasTrait;

    public function index(Request $request)
    {
        $totalAset = Barang::count();

        $rusak = Barang::whereHas('latestKondisi', function ($query) {
            $query->whereIn('status_kondisi', ['Rusak Ringan', 'Rusak Berat']);
        })->count();

        $query = Barang::with(['karyawan', 'latestKondisi']);

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_barcode', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('sort')) {
            if ($request->sort == 'terlama') {
                $query->oldest('id_barang');
            } elseif ($request->sort == 'nama_asc') {
                $query->orderBy('nama_barang', 'asc');
            } elseif ($request->sort == 'nama_desc') {
                $query->orderBy('nama_barang', 'desc');
            } else {
                $query->latest('id_barang');
            }
        } else {
            $query->latest('id_barang');
        }

        $perPage = $request->input('per_page', 10);
        $barang = $query->paginate($perPage);
        
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
            'id_kontrak'           => 'required|exists:m_kontrak,id_kontrak',
            'nama_barang'          => 'required|string',
            'kategori'             => 'required|in:Elektronik,Furnitur,Jaringan,Kendaraan,Peralatan Kantor,Lainnya',
            'spesifikasi'          => 'required|string',
            'jumlah_barang'        => 'required|numeric|min:1',
            'status_penguasaan'    => 'required|in:personal,lokasi',
            'lokasi_fisik'         => 'required_if:status_penguasaan,lokasi|nullable|string',
            'nip'                  => 'required_if:status_penguasaan,personal|nullable|exists:m_karyawan,nip',
            'dokumentasi_barang'   => 'nullable|array',
            'dokumentasi_barang.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $dokumentasiPaths = null;
        if ($request->hasFile('dokumentasi_barang')) {
            $dokumentasiPaths = [];
            foreach ($request->file('dokumentasi_barang') as $file) {
                $dokumentasiPaths[] = $file->store('dokumentasi', 'public');
            }
        }

        $kontrak = Kontrak::findOrFail($validatedData['id_kontrak']);

        $prefixes = [
            'Elektronik'       => 'EL',
            'Furnitur'         => 'FR',
            'Jaringan'         => 'JR',
            'Kendaraan'        => 'KD',
            'Peralatan Kantor' => 'PK',
            'Lainnya'          => 'LN',
        ];
        
        $prefix = $prefixes[$validatedData['kategori']] ?? 'LN';

        $barangs = DB::transaction(function () use ($request, $validatedData, $dokumentasiPaths, $kontrak, $prefix) {
            
            $idKaryawanPemegang = null;
            if ($validatedData['status_penguasaan'] == 'personal' && !empty($validatedData['nip'])) {
                $karyawan = Karyawan::where('nip', $validatedData['nip'])->first();
                $idKaryawanPemegang = $karyawan->id_karyawan;
            }

            $baseNumber = Barang::max('id_barang') ?? 0;
            $createdBarangs = [];

            for ($i = 1; $i <= $validatedData['jumlah_barang']; $i++) {
                $currentCount = $baseNumber + $i;
                $kodeBarcode = sprintf('%s-%04d-%s', $prefix, $currentCount, $kontrak->no_kontrak);

                $barangData = [
                    'id_kontrak'         => $validatedData['id_kontrak'],
                    'kode_barcode'       => $kodeBarcode,
                    'nama_barang'        => $validatedData['nama_barang'],
                    'kategori'           => $validatedData['kategori'],
                    'spesifikasi'        => $validatedData['spesifikasi'],
                    'dokumentasi_barang' => $dokumentasiPaths,
                ];

                if ($validatedData['status_penguasaan'] == 'personal') {
                    $barangData['id_karyawan_pemegang'] = $idKaryawanPemegang;
                    $barangData['lokasi_fisik'] = null;
                } else {
                    $barangData['lokasi_fisik'] = $validatedData['lokasi_fisik'];
                    $barangData['id_karyawan_pemegang'] = null;
                }

                $barang = Barang::create($barangData);
                $createdBarangs[] = $barang;

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
            }

            $this->catatLog(
                'Barang', 
                'Create', 
                'Menambah ' . $validatedData['jumlah_barang'] . ' data barang baru (' . $validatedData['nama_barang'] . ')'
            );

            return $createdBarangs;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Barang berhasil ditambah.',
            'data'    => $barangs
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
            'kategori'             => 'required|in:Elektronik,Furnitur,Jaringan,Kendaraan,Peralatan Kantor,Lainnya',
            'spesifikasi'          => 'required|string',
            'status_penguasaan'    => 'required|in:personal,lokasi',
            'lokasi_fisik'         => 'required_if:status_penguasaan,lokasi|nullable|string',
            'nip'                  => 'required_if:status_penguasaan,personal|nullable|exists:m_karyawan,nip',
            'kondisi'              => 'nullable|string',
            'catatan'              => 'nullable|string',
            'dokumentasi_barang'   => 'nullable|array',
            'dokumentasi_barang.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $dokumentasiPaths = $barang->dokumentasi_barang;
        if ($request->hasFile('dokumentasi_barang')) {
            if (!empty($barang->dokumentasi_barang)) {
                foreach ($barang->dokumentasi_barang as $oldFile) {
                    Storage::disk('public')->delete($oldFile);
                }
            }
            
            $dokumentasiPaths = [];
            foreach ($request->file('dokumentasi_barang') as $file) {
                $dokumentasiPaths[] = $file->store('dokumentasi', 'public');
            }
        }

        $barang = DB::transaction(function () use ($request, $barang, $validatedData, $dokumentasiPaths) {
            $oldKaryawanId = $barang->getOriginal('id_karyawan_pemegang');
            $oldLokasiFisik = $barang->getOriginal('lokasi_fisik');
            $oldAsal = $oldKaryawanId ? ($barang->karyawan?->nama_karyawan ?? $oldKaryawanId) : $oldLokasiFisik;

            $idKaryawanPemegang = null;
            if ($validatedData['status_penguasaan'] == 'personal' && !empty($validatedData['nip'])) {
                $karyawan = Karyawan::where('nip', $validatedData['nip'])->first();
                $idKaryawanPemegang = $karyawan->id_karyawan;
            }

            $barang->fill([
                'id_kontrak'         => $validatedData['id_kontrak'],
                'nama_barang'        => $validatedData['nama_barang'],
                'kategori'           => $validatedData['kategori'],
                'spesifikasi'        => $validatedData['spesifikasi'],
                'dokumentasi_barang' => $dokumentasiPaths,
            ]);

            if ($validatedData['status_penguasaan'] == 'personal') {
                $barang->lokasi_fisik = null;
                $barang->id_karyawan_pemegang = $idKaryawanPemegang;

                if ($barang->isDirty('id_karyawan_pemegang') || $oldLokasiFisik !== null) {
                    Mobilisasi::create([
                        'id_barang'        => $barang->id_barang,
                        'asal'             => $oldAsal ?? '-',
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
                        'asal'             => $oldAsal ?? '-',
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

            $this->catatLog(
                'Barang', 
                'Update', 
                'Memperbarui data barang dengan kode barcode: ' . $barang->kode_barcode
            );

            return $barang;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Barang berhasil diperbarui.',
            'data'    => $barang
        ], 200);
    }

    public function destroy(Request $request, string $id)
    {
        $barang = Barang::findOrFail($id);
        $kodeBarcode = $barang->kode_barcode;
        
        if (!empty($barang->dokumentasi_barang)) {
            foreach ($barang->dokumentasi_barang as $file) {
                Storage::disk('public')->delete($file);
            }
        }

        $barang->delete();

        $this->catatLog(
            'Barang', 
            'Delete', 
            'Menghapus data barang dengan kode barcode: ' . $kodeBarcode
        );
        
        return response()->json([
            'status'  => 'success',
            'message' => 'Data Barang berhasil dihapus.',
        ], 200);
    }
}