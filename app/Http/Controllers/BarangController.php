<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kondisi;
use App\Models\Kontrak;
use App\Traits\LogAktivitasTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS2D;

class BarangController extends Controller
{
    use LogAktivitasTrait;

    public function index(Request $request)
    {
        $query = Barang::with(['karyawan', 'kontrak', 'latestKondisi']);

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

        $barang = $query->paginate(10);
        $kontrak = Kontrak::latest()->get();
        
        return view('barang.index', compact('barang', 'kontrak'));
    }

    public function getByKontrak($id_kontrak)
    {
        $barang = Barang::where('id_kontrak', $id_kontrak)
                    ->select('id_barang', 'nama_barang', 'kode_barcode')
                    ->orderBy('nama_barang')
                    ->get();

        return response()->json($barang);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kontrak'           => 'required|exists:m_kontrak,id_kontrak',
            'nama_barang'          => 'required|string',
            'kategori'             => 'required|in:Elektronik,Furnitur,Jaringan,Kendaraan,Peralatan Kantor,Lainnya',
            'spesifikasi'          => 'required|string',
            'jumlah_barang'        => 'required|numeric|min:1',
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

        $kontrak = Kontrak::findOrFail($validated['id_kontrak']);

        $prefixes = [
            'Elektronik'       => 'EL',
            'Furnitur'         => 'FR',
            'Jaringan'         => 'JR',
            'Kendaraan'        => 'KD',
            'Peralatan Kantor' => 'PK',
            'Lainnya'          => 'LN',
        ];
        
        $prefix = $prefixes[$validated['kategori']] ?? 'LN';

        DB::transaction(function () use ($validated, $dokumentasiPaths, $kontrak, $prefix) {
            
            $baseNumber = Barang::max('id_barang') ?? 0;

            for ($i = 1; $i <= $validated['jumlah_barang']; $i++) {
                $currentCount = $baseNumber + $i;
                $kodeBarcode = sprintf('%s-%04d-%s', $prefix, $currentCount, $kontrak->no_kontrak);

                $barang = Barang::create([
                    'id_kontrak'         => $validated['id_kontrak'],
                    'kode_barcode'       => $kodeBarcode,
                    'nama_barang'        => $validated['nama_barang'],
                    'kategori'           => $validated['kategori'],
                    'spesifikasi'        => $validated['spesifikasi'],
                    'dokumentasi_barang' => $dokumentasiPaths,
                ]);

                Kondisi::create([
                    'id_barang'        => $barang->id_barang,
                    'id_user_operator' => Auth::id(),
                    'status_kondisi'   => 'Baik',
                    'catatan'          => 'Registrasi Awal',
                ]);
            }

            $this->catatLog(
                'Barang', 
                'Create', 
                'Menambah ' . $validated['jumlah_barang'] . ' data barang baru (' . $validated['nama_barang'] . ')'
            );
        });

        return redirect()->route('barang.index')->with('success', 'Data Barang berhasil ditambahkan dan Menunggu Serah Terima.');
    }

    public function show($id)
    {
        $barang = Barang::with([
            'kontrak', 
            'karyawan', 
            'kondisi.operator.karyawan',
            'latestKondisi.operator.karyawan',
            'mobilisasi.penerima',
            'mobilisasi.operator.karyawan'
        ])->findOrFail($id);
        
        $barcode = new DNS2D();
        $barang->qr_html = $barcode->getBarcodeHTML((string)$barang->kode_barcode, 'QRCODE', 10, 10);
        
        return response()->json($barang);
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'id_kontrak'           => 'required|exists:m_kontrak,id_kontrak',
            'nama_barang'          => 'required|string',
            'kategori'             => 'required|in:Elektronik,Furnitur,Jaringan,Kendaraan,Peralatan Kantor,Lainnya',
            'spesifikasi'          => 'required|string',
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

        DB::transaction(function () use ($validated, $barang, $dokumentasiPaths) {
            $barang->fill([
                'id_kontrak'         => $validated['id_kontrak'],
                'nama_barang'        => $validated['nama_barang'],
                'kategori'           => $validated['kategori'],
                'spesifikasi'        => $validated['spesifikasi'],
                'dokumentasi_barang' => $dokumentasiPaths,
            ]);
            $barang->save();

            if (!empty($validated['kondisi'])) {
                Kondisi::create([
                    'id_barang'        => $barang->id_barang,
                    'id_user_operator' => Auth::id(),
                    'status_kondisi'   => $validated['kondisi'],
                    'catatan'          => $validated['catatan'] ?? null,
                ]);
            }

            $this->catatLog(
                'Barang', 
                'Update', 
                'Memperbarui data barang dengan kode barcode: ' . $barang->kode_barcode
            );
        });

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
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
        
        return redirect()->route('barang.index')->with('success', 'Data Barang berhasil dihapus.');
    }

    public function printLabel($id)
    {
        $barang = Barang::with(['karyawan'])->findOrFail($id);
        $barcode = new DNS2D();
        
        $qrData = $barcode->getBarcodePNG((string)$barang->kode_barcode, 'QRCODE', 10, 10);
        $barang->qr_html = '<img src="data:image/png;base64,' . $qrData . '" style="width: 100px; height: 100px;">';

        return view('barang.print_single', compact('barang'));
    }

    public function printAll(Request $request)
    {
        $barang = Barang::all();
        $barcode = new DNS2D();

        foreach($barang as $item) {
            $qrData = $barcode->getBarcodePNG((string)$item->kode_barcode, 'QRCODE', 10, 10);
            $item->qr_html = '<img src="data:image/png;base64,' . $qrData . '" style="width: 80px; height: 80px;">';
        }

        return view('barang.print_all', compact('barang'));
    }
}