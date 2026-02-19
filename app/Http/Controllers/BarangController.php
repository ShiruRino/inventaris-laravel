<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Kontrak;
use App\Models\Mobilisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Milon\Barcode\DNS2D;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barang = Barang::with(['karyawan', 'kontrak'])->latest()->paginate(10);
        $kontrak = Kontrak::all(); // For dropdown
        $karyawan = Karyawan::all(); // For dropdown
        
        return view('barang.index', compact('barang', 'kontrak', 'karyawan'));
    }
    public function getByKontrak($id_kontrak)
    {
        // Fetch only available items or all items based on your logic
        $barang = \App\Models\Barang::where('id_kontrak', $id_kontrak)
                    ->select('id_barang', 'nama_barang', 'kode_barcode') // Select only needed columns
                    ->orderBy('nama_barang')
                    ->get();

        return response()->json($barang);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'id_kontrak' => 'required|exists:m_kontrak,id_kontrak',
            'kode_barcode' => 'required',
            'nama_barang' => 'required',
            'spesifikasi' => 'required',
            'jumlah_barang' => 'required|numeric',
            'lokasi_fisik' => 'nullable',
            'id_karyawan_pemegang' => 'nullable|exists:m_karyawan,id_karyawan',
        ];
        $validatedData = Validator::make($request->all(), $rules);
        if($validatedData->fails()){
            return back()->withErrors($validatedData)->withInput();
        }
        if(!$request->has('id_karyawan_pemegang') && !$request->has('lokasi_fisik')){
            return back()->with('error', 'Penempatan awal belum diisi')->withInput();
        }
        $barang = Barang::create($request->all());
        if($request->id_karyawan_pemegang){
            Mobilisasi::create([
                'id_barang'=> $barang->id_barang,
                'asal'=> '(Vendor)',
                'id_penerima'=> $request->id_karyawan_pemegang,
                'id_user_operator' => Auth::user()->id_user,
            ]);
        }
        return redirect()->route('barang.index')->with('success','Data Barang berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $barang = Barang::with(['kontrak', 'karyawan', 'kondisi'])->findOrFail($id);
        $barcode = new DNS2D(); 
    
        $barang->qr_html = $barcode->getBarcodeHTML((string)$barang->kode_barcode, 'QRCODE', 10, 10);
        return response()->json($barang);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        $barang->update($request->all());
        $barang->save();
        return redirect()->route('barang.index')->with('success','Data barang berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        $barang->delete();
        return redirect()->route('barang.index')->with('success','Data Barang berhasil dihapus.');
    }
    public function printLabel($id)
    {
        $barang = Barang::with(['karyawan'])->findOrFail($id);
        
        $barcode = new DNS2D();
        
        // 1. Get the Base64 String (The text you saw on screen)
        $qrData = $barcode->getBarcodePNG((string)$barang->kode_barcode, 'QRCODE', 10, 10);
        
        // 2. Wrap it in an Image Tag so the browser renders it as a picture
        // Note: getBarcodePNG returns Base64, so we DON'T need to base64_encode it again.
        $barang->qr_html = '<img src="data:image/png;base64,' . $qrData . '" style="width: 100px; height: 100px;">';

        return view('barang.print_single', compact('barang'));
    }

    public function printAll(Request $request)
    {
        // 1. Fetch all items (eager load relation for performance)
        $barang = Barang::all();
        
        $barcode = new DNS2D();

        // 2. Loop through every item to generate its QR code
        foreach($barang as $item) {
            // Get the Base64 String
            $qrData = $barcode->getBarcodePNG((string)$item->kode_barcode, 'QRCODE', 10, 10);
            
            // Create the HTML Image tag
            $item->qr_html = '<img src="data:image/png;base64,' . $qrData . '" style="width: 80px; height: 80px;">';
        }

        return view('barang.print_all', compact('barang'));
    }
}
