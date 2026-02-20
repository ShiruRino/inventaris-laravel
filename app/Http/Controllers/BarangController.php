<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Kondisi;
use App\Models\Kontrak;
use App\Models\Mobilisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS2D;

class BarangController extends Controller
{
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
            'id_kontrak'        => 'required|exists:m_kontrak,id_kontrak',
            'kode_barcode'      => 'required|unique:m_barang,kode_barcode',
            'nama_barang'       => 'required|string',
            'kategori'          => 'required|in:Elektronik,Furnitur,Jaringan,Kendaraan,Peralatan Kantor,Lainnya',
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

        DB::transaction(function () use ($validated, $imagePath) {
            
            $idKaryawanPemegang = null;
            if ($validated['status_penguasaan'] == 'personal' && !empty($validated['nip'])) {
                $karyawan = Karyawan::where('nip', $validated['nip'])->first();
                $idKaryawanPemegang = $karyawan->id_karyawan;
            }

            $barangData = [
                'id_kontrak'    => $validated['id_kontrak'],
                'kode_barcode'  => $validated['kode_barcode'],
                'nama_barang'   => $validated['nama_barang'],
                'kategori'      => $validated['kategori'],
                'spesifikasi'   => $validated['spesifikasi'],
                'jumlah_barang' => $validated['jumlah_barang'],
                'foto_barang'   => $imagePath,
            ];

            if ($validated['status_penguasaan'] == 'personal') {
                $barangData['id_karyawan_pemegang'] = $idKaryawanPemegang;
                $barangData['lokasi_fisik'] = null;
            } else {
                $barangData['lokasi_fisik'] = $validated['lokasi_fisik'];
                $barangData['id_karyawan_pemegang'] = null;
            }

            $barang = Barang::create($barangData);

            Mobilisasi::create([
                'id_barang'        => $barang->id_barang,
                'asal'             => '(Vendor)',
                'id_penerima'      => $idKaryawanPemegang,
                'lokasi_tujuan'    => $validated['status_penguasaan'] == 'lokasi' ? $validated['lokasi_fisik'] : null,
                'id_user_operator' => Auth::id(), 
            ]);

            Kondisi::create([
                'id_barang'        => $barang->id_barang,
                'id_user_operator' => Auth::id(),
                'status_kondisi'   => 'Baik',
                'catatan'          => 'Registrasi Awal',
            ]);
        });

        return redirect()->route('barang.index')->with('success', 'Data Barang berhasil ditambahkan.');
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
            'id_kontrak'        => 'required|exists:m_kontrak,id_kontrak',
            'nama_barang'       => 'required|string',
            'kategori'          => 'required|in:Elektronik,Furnitur,Jaringan,Kendaraan,Peralatan Kantor,Lainnya',
            'spesifikasi'       => 'required|string',
            'jumlah_barang'     => 'required|numeric|min:1',
            'status_penguasaan' => 'required|in:personal,lokasi',
            'lokasi_fisik'      => 'required_if:status_penguasaan,lokasi|nullable|string',
            'nip'               => 'required_if:status_penguasaan,personal|nullable|exists:m_karyawan,nip',
            'kondisi'           => 'nullable|string',
            'catatan'           => 'nullable|string',
            'foto_barang'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = $barang->foto_barang;
        if ($request->hasFile('foto_barang')) {
            if ($barang->foto_barang) {
                Storage::disk('public')->delete($barang->foto_barang);
            }
            $imagePath = $request->file('foto_barang')->store('barang', 'public');
        }

        DB::transaction(function () use ($validated, $barang, $imagePath) {
            $oldKaryawanId = $barang->getOriginal('id_karyawan_pemegang');
            $oldLokasiFisik = $barang->getOriginal('lokasi_fisik');
            $oldAsal = $oldKaryawanId ? ($barang->karyawan?->nama_karyawan ?? $oldKaryawanId) : $oldLokasiFisik;

            $idKaryawanPemegang = null;
            if ($validated['status_penguasaan'] == 'personal' && !empty($validated['nip'])) {
                $karyawan = Karyawan::where('nip', $validated['nip'])->first();
                $idKaryawanPemegang = $karyawan->id_karyawan;
            }

            $barang->fill([
                'id_kontrak'    => $validated['id_kontrak'],
                'nama_barang'   => $validated['nama_barang'],
                'kategori'      => $validated['kategori'],
                'jumlah_barang' => $validated['jumlah_barang'],
                'spesifikasi'   => $validated['spesifikasi'],
                'foto_barang'   => $imagePath,
            ]);

            if ($validated['status_penguasaan'] == 'personal') {
                $barang->lokasi_fisik = null;
                $barang->id_karyawan_pemegang = $idKaryawanPemegang;

                if ($barang->isDirty('id_karyawan_pemegang') || $oldLokasiFisik !== null) {
                    Mobilisasi::create([
                        'id_barang'        => $barang->id_barang,
                        'asal'             => $oldAsal ?? '-',
                        'id_penerima'      => $idKaryawanPemegang,
                        'id_user_operator' => Auth::id(),
                    ]);
                }
            } elseif ($validated['status_penguasaan'] == 'lokasi') {
                $barang->id_karyawan_pemegang = null;
                $barang->lokasi_fisik = $validated['lokasi_fisik'];

                if ($barang->isDirty('lokasi_fisik') || $oldKaryawanId !== null) {
                    Mobilisasi::create([
                        'id_barang'        => $barang->id_barang,
                        'asal'             => $oldAsal ?? '-',
                        'lokasi_tujuan'    => $validated['lokasi_fisik'],
                        'id_user_operator' => Auth::id(),
                    ]);
                }
            }

            $barang->save();

            if (!empty($validated['kondisi'])) {
                Kondisi::create([
                    'id_barang'        => $barang->id_barang,
                    'id_user_operator' => Auth::id(),
                    'status_kondisi'   => $validated['kondisi'],
                    'catatan'          => $validated['catatan'] ?? null,
                ]);
            }
        });

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        if ($barang->foto_barang) {
            Storage::disk('public')->delete($barang->foto_barang);
        }

        $barang->delete();
        
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