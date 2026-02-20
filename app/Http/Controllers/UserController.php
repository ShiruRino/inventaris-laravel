<?php
namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('karyawan');

        if ($request->has('search') && $request->search != '') {
            $query->where('username', 'like', '%' . $request->search . '%')
                  ->orWhereHas('karyawan', function($q) use ($request) {
                      $q->where('nama_karyawan', 'like', '%' . $request->search . '%')
                        ->orWhere('nip', 'like', '%' . $request->search . '%');
                  });
        }

        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        if ($request->has('sort')) {
            if ($request->sort == 'terlama') {
                $query->oldest('id_user');
            } elseif ($request->sort == 'username_asc') {
                $query->orderBy('username', 'asc');
            } elseif ($request->sort == 'username_desc') {
                $query->orderBy('username', 'desc');
            } else {
                $query->latest('id_user');
            }
        } else {
            $query->latest('id_user');
        }

        $users = $query->paginate(10);
        
        return view("users.index", compact("users"));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip'              => 'required|exists:m_karyawan,nip',
            'username'         => 'required|min:3|unique:m_user,username',
            'password'         => 'required|min:6',
            'confirm_password' => 'required|same:password',
            'role'             => 'required|in:admin,lapangan'
        ]);

        $karyawan = Karyawan::where('nip', $validated['nip'])->first();

        if (User::where('id_karyawan', $karyawan->id_karyawan)->exists()) {
            return back()->with('error', 'Karyawan ini sudah memiliki akun.')->withInput();
        }

        User::create([
            'id_karyawan' => $karyawan->id_karyawan,
            'username'    => $validated['username'],
            'password'    => Hash::make($validated['password']),
            'role'        => $validated['role'],
        ]);

        return redirect()->route('user.index')->with('success', 'Data User berhasil ditambah.');
    }

    public function show(User $user)
    {
        return response()->json($user->load('karyawan'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                Rule::unique('m_user', 'username')->ignore($user->id_user, 'id_user'),
            ],
            'password'         => 'nullable|min:6',
            'confirm_password' => 'nullable|same:password',
            'role'             => 'required|in:admin,lapangan',
        ]);

        $updateData = [
            'username'    => $validated['username'],
            'role'        => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('user.index')->with('success', 'Data User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()->route('user.index')->with('success', 'Data User berhasil dihapus.');
    }
}