<?php
namespace App\Http\Controllers;

use App\Exports\MobilisasiExport;
use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Kontrak;
use App\Models\Mobilisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MobilisasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Mobilisasi::with(['barang', 'penerima', 'operator.karyawan']);

        if ($request->has('search') && $request->search != '') {
            $query->whereHas('barang', function($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_barcode', 'like', '%' . $request->search . '%');
            })->orWhereHas('penerima', function($q) use ($request) {
                $q->where('nama_karyawan', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $mobilisasi = $query->latest('id_mobilisasi')->paginate(10);
        
        $karyawan = Karyawan::all();
        $kontrak = Kontrak::latest()->get(); 

        return view('mobilisasi.index', compact('mobilisasi', 'karyawan', 'kontrak'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_barang'          => 'required|exists:m_barang,id_barang',
            'jenis_transaksi'    => 'required|in:karyawan,lokasi',
            'nip_penerima'       => 'required_if:jenis_transaksi,karyawan|nullable|exists:m_karyawan,nip',
            'lokasi_tujuan'      => 'required_if:jenis_transaksi,lokasi|nullable|string',
            'bukti_serah_terima' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $barang = Barang::findOrFail($validated['id_barang']);
        $idPenerima = null;

        if ($validated['jenis_transaksi'] == 'karyawan' && !empty($validated['nip_penerima'])) {
            $karyawanPenerima = Karyawan::where('nip', $validated['nip_penerima'])->firstOrFail();
            $idPenerima = $karyawanPenerima->id_karyawan;
            
            if ($barang->id_karyawan_pemegang === $idPenerima) {
                return back()->with('error', 'Barang sudah berada di penguasaan karyawan tersebut.')->withInput();
            }
        } elseif ($validated['jenis_transaksi'] == 'lokasi' && !empty($validated['lokasi_tujuan'])) {
            if ($barang->lokasi_fisik === $validated['lokasi_tujuan']) {
                return back()->with('error', 'Barang sudah berada di lokasi tersebut.')->withInput();
            }
        }

        $asalString = $barang->id_karyawan_pemegang 
            ? ($barang->karyawan?->nama_karyawan ?? $barang->karyawan?->nip) 
            : ($barang->lokasi_fisik ?? '-');
    

        $buktiPath = null;
        if ($request->hasFile('bukti_serah_terima')) {
            $buktiPath = $request->file('bukti_serah_terima')->store('bukti_mobilisasi', 'public');
        }

        DB::transaction(function () use ($validated, $barang, $asalString, $idPenerima, $buktiPath) {
            
            if ($validated['jenis_transaksi'] == 'karyawan') {
                Mobilisasi::create([
                    'id_barang'          => $barang->id_barang,
                    'asal'               => $asalString,
                    'id_penerima'        => $idPenerima,
                    'id_user_operator'   => Auth::id(),
                    'bukti_serah_terima' => $buktiPath,
                ]);

                $barang->update([
                    'lokasi_fisik'         => null,
                    'id_karyawan_pemegang' => $idPenerima,
                ]);
            } 
            elseif ($validated['jenis_transaksi'] == 'lokasi') {
                Mobilisasi::create([
                    'id_barang'          => $barang->id_barang,
                    'asal'               => $asalString,
                    'lokasi_tujuan'      => $validated['lokasi_tujuan'],
                    'id_user_operator'   => Auth::id(),
                    'bukti_serah_terima' => $buktiPath,
                ]);

                $barang->update([
                    'id_karyawan_pemegang' => null,
                    'lokasi_fisik'         => $validated['lokasi_tujuan'],
                ]);
            }
        });

        return redirect()->route('mobilisasi.index')->with('success', 'Data Mobilisasi berhasil ditambah.');
    }

    public function getKaryawanByNip($nip)
    {
        $karyawan = Karyawan::where('nip', $nip)->first();

        if ($karyawan) {
            return response()->json([
                'status'        => 'success',
                'id_karyawan'   => $karyawan->id_karyawan,
                'nama_karyawan' => $karyawan->nama_karyawan,
                'jabatan'       => $karyawan->jabatan ?? '-'
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'NIP tidak ditemukan'
        ], 404);
    }
    
    public function export(Request $request)
    {
        $namaFile = 'Riwayat_Mobilisasi_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new MobilisasiExport(
            $request->search,
            $request->start_date,
            $request->end_date
        ), $namaFile);
    }
}