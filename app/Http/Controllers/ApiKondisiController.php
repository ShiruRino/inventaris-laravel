<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kondisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApiKondisiController extends Controller
{
    public function index(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        $perPage = $request->input('per_page', 10);
        
        $kondisi = Kondisi::latest()->where('id_barang', $barang->id_barang)->paginate($perPage);
        
        return response()->json([
            'barang'  => $barang,
            'kondisi' => $kondisi
        ]);
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'status_kondisi' => 'required|string',
            'catatan'        => 'nullable|string',
            'foto_kondisi'   => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $barang = Barang::findOrFail($id);

        $fotoPath = null;
        if ($request->hasFile('foto_kondisi')) {
            $fotoPath = $request->file('foto_kondisi')->store('foto_kondisi', 'public');
        }

        $kondisi = Kondisi::create([
            'id_barang'        => $barang->id_barang,
            'id_user_operator' => $request->user()->id_user,
            'status_kondisi'   => $validated['status_kondisi'],
            'catatan'          => $validated['catatan'] ?? null,
            'foto_kondisi'     => $fotoPath,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Kondisi berhasil ditambah.',
            'data'    => $kondisi
        ], 201);
    }

    public function show(string $id)
    {
        $kondisi = Kondisi::with('barang')->findOrFail($id);
        
        return response()->json($kondisi);
    }

    public function update(Request $request, string $id)
    {
        $kondisi = Kondisi::findOrFail($id);

        $validated = $request->validate([
            'status_kondisi' => 'sometimes|required|string',
            'catatan'        => 'nullable|string',
            'foto_kondisi'   => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($request->hasFile('foto_kondisi')) {
            if ($kondisi->foto_kondisi) {
                Storage::disk('public')->delete($kondisi->foto_kondisi);
            }
            $validated['foto_kondisi'] = $request->file('foto_kondisi')->store('foto_kondisi', 'public');
        }

        $kondisi->update($validated);
        $kondisi->load('barang');

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Kondisi berhasil diperbarui.',
            'data'    => $kondisi
        ], 200);
    }

    public function destroy(string $id)
    {
        $kondisi = Kondisi::findOrFail($id);

        if ($kondisi->foto_kondisi) {
            Storage::disk('public')->delete($kondisi->foto_kondisi);
        }

        $kondisi->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Kondisi berhasil dihapus.',
        ], 200);
    }
}