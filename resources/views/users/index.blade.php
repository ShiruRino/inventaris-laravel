@extends('layouts.app')

@section('title', 'Data User')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Manajemen User (Hak Akses)</h3>
            <p class="text-muted">Kelola akun login untuk Web Admin dan Petugas Lapangan.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="bi bi-person-plus-fill"></i> Buat Akun Baru
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>
            <strong>Penting:</strong> Sebelum membuat User, pastikan data orang tersebut sudah terdaftar di menu <u>Karyawan</u>.
        </div>
    </div>

    <form action="{{ route('user.index') }}" method="GET" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari NIP, Nama, atau Username..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Hak Akses</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="lapangan" {{ request('role') == 'lapangan' ? 'selected' : '' }}>Petugas Lapangan</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
                    <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama Ditambahkan</option>
                    <option value="username_asc" {{ request('sort') == 'username_asc' ? 'selected' : '' }}>Username (A-Z)</option>
                    <option value="username_desc" {{ request('sort') == 'username_desc' ? 'selected' : '' }}>Username (Z-A)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                <a href="{{ route('user.index') }}" class="btn btn-light border"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">No</th>
                            <th>Username</th>
                            <th>Nama Karyawan (Pemilik Akun)</th>
                            <th>Level Akses</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td class="ps-3">{{ $users->firstItem() + $index }}</td>
                                <td class="fw-bold">{{ $user->username }}</td>
                                <td>
                                    {{ $user->karyawan->nama_karyawan ?? 'Data Tidak Ditemukan' }} 
                                    <span class="text-muted small">({{ $user->karyawan->jabatan ?? '-' }})</span>
                                </td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-primary">Administrator</span>
                                    @else
                                        <span class="badge bg-info text-dark">Petugas Lapangan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_online)
                                        <span class="badge bg-success rounded-pill">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Online
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">Offline</span>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            {{ $user->last_activity_at ? $user->last_activity_at->diffForHumans() : 'Belum pernah login' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-info" onclick="showDetail({{ $user->id_user }})" title="Detail Activity"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="showEdit({{ $user->id_user }})" title="Edit Akses"><i class="bi bi-pencil-square"></i></button>
                                    <form action="{{ route('user.destroy', $user->id_user) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data user terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-center mt-3">
        {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrasi User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.store') }}" method="POST" id="formUser">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">NIP Karyawan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nip" id="tambah_nip" value="{{ old('nip') }}" placeholder="Masukkan NIP Karyawan yang terdaftar" required autocomplete="off">
                        <div id="feedback_nip" class="form-text mt-2 text-muted">Ketik NIP untuk mengecek ketersediaan data karyawan.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" value="{{ old('username') }}" placeholder="Tanpa spasi, contoh: budi_ga" required minlength="3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="inputPassword" required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('inputPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="confirm_password" id="inputConfirmPassword" required minlength="6">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('inputConfirmPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level Akses <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="lapangan" {{ old('role') == 'lapangan' ? 'selected' : '' }}>Petugas Lapangan (Akses Mobile App)</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator (Akses Web Panel)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitUser" disabled>Buat Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title">Edit Akses User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formEditUser">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Pemilik Akun (Readonly)</label>
                        <input type="text" class="form-control bg-light" id="edit_nama_karyawan" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required minlength="3">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ganti Password (Opsional)</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="edit_password" placeholder="Kosongkan jika tidak ingin mengganti" minlength="6">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('edit_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="confirm_password" id="edit_confirm_password" placeholder="Kosongkan jika tidak ingin mengganti" minlength="6">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('edit_confirm_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Level Akses</label>
                            <select class="form-select" name="role" id="edit_level" required>
                                <option value="lapangan">Petugas Lapangan</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailUser" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Detail Akun Pengguna</h5>
                    <small class="text-muted" id="detail_username_header">Loading...</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5 border-end">
                        <div class="text-center mb-3">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-person-fill h1 text-secondary m-0"></i>
                            </div>
                            <h5 class="mt-2 mb-0" id="detail_nama">Loading...</h5>
                            <span class="badge bg-secondary mt-1" id="detail_level">Loading...</span>
                        </div>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">NIP</span>
                                <span class="fw-bold" id="detail_nip">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Jabatan</span>
                                <span class="fw-bold" id="detail_jabatan">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Divisi</span>
                                <span class="fw-bold" id="detail_divisi">-</span>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-7">
                        <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Aktivitas Terakhir</h6>
                        <div class="list-group list-group-flush border rounded mb-3" id="detail_log_aktivitas">
                            </div>

                        <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>Keamanan & Login</h6>
                        <div class="alert alert-light border small p-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Login Terakhir:</span>
                                <span id="detail_last_login" class="fw-bold">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Aktivitas Terakhir:</span>
                                <span id="detail_last_activity" class="fw-bold">-</span>
                            </div>
                            <hr class="my-2">
                            <p class="mb-0 text-muted">Akses: <span id="detail_role_text" class="fw-bold text-dark"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="detail_id_user_temp">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning" onclick="switchModalToEdit()">Edit User Ini</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword(inputId, btnElement) {
        const passwordInput = document.getElementById(inputId);
        const icon = btnElement.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    async function showDetail(id) {
        try {
            const response = await fetch(`/user/${id}`);
            const data = await response.json();
            
            document.getElementById('detail_username_header').innerText = '@' + data.username;
            document.getElementById('detail_nama').innerText = data.karyawan ? data.karyawan.nama_karyawan : 'Tidak Diketahui';
            
            const levelText = data.role === 'admin' ? 'Administrator' : 'Petugas Lapangan';
            document.getElementById('detail_level').innerText = levelText;
            document.getElementById('detail_level').className = data.role === 'admin' ? 'badge bg-primary mt-1' : 'badge bg-info text-dark mt-1';
            
            document.getElementById('detail_nip').innerText = data.karyawan ? data.karyawan.nip : '-';
            document.getElementById('detail_jabatan').innerText = data.karyawan ? data.karyawan.jabatan : '-';
            document.getElementById('detail_divisi').innerText = data.karyawan ? data.karyawan.divisi : '-';
            document.getElementById('detail_role_text').innerText = levelText;

            document.getElementById('detail_last_login').innerText = data.last_login_at ? new Date(data.last_login_at).toLocaleString('id-ID') : 'Belum Pernah';
            document.getElementById('detail_last_activity').innerText = data.last_activity_at ? new Date(data.last_activity_at).toLocaleString('id-ID') : 'Tidak Ada';

            const logContainer = document.getElementById('detail_log_aktivitas');
            logContainer.innerHTML = '';

            if (data.riwayat_aktivitas && data.riwayat_aktivitas.length > 0) {
                data.riwayat_aktivitas.forEach(log => {
                    const logItem = `
                        <div class="list-group-item p-2">
                            <div class="d-flex justify-content-between">
                                <small class="fw-bold text-dark">${log.aktivitas || 'Melakukan aksi'}</small>
                                <small class="text-muted" style="font-size: 0.7rem;">${new Date(log.created_at).toLocaleTimeString('id-ID')}</small>
                            </div>
                        </div>`;
                    logContainer.innerHTML += logItem;
                });
            } else {
                logContainer.innerHTML = '<div class="list-group-item text-center text-muted small py-3">Tidak ada riwayat aktivitas terbaru</div>';
            }

            document.getElementById('detail_id_user_temp').value = id;
            new bootstrap.Modal(document.getElementById('modalDetailUser')).show();
        } catch (error) {
            console.error(error);
            alert('Gagal mengambil data detail user.');
        }
    }

    async function showEdit(id) {
        try {
            const response = await fetch(`/user/${id}`);
            const data = await response.json();
            
            document.getElementById('formEditUser').action = `/user/${id}`;
            document.getElementById('edit_nama_karyawan').value = data.karyawan ? `${data.karyawan.nama_karyawan} (${data.karyawan.jabatan})` : 'Data Tidak Ditemukan';
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_level').value = data.role;

            const detailModalEl = document.getElementById('modalDetailUser');
            const detailModal = bootstrap.Modal.getInstance(detailModalEl);
            if(detailModal) detailModal.hide();

            new bootstrap.Modal(document.getElementById('modalEditUser')).show();
        } catch (error) {
            alert('Gagal mengambil data untuk diedit.');
        }
    }

    function switchModalToEdit() {
        const id = document.getElementById('detail_id_user_temp').value;
        if(id) {
            showEdit(id);
        }
    }

    let nipTimeout;
    const nipInput = document.getElementById('tambah_nip');
    const nipFeedback = document.getElementById('feedback_nip');
    const submitBtn = document.getElementById('btnSubmitUser');

    nipInput.addEventListener('input', function() {
        clearTimeout(nipTimeout);
        const nip = this.value.trim();

        if (nip.length === 0) {
            nipFeedback.innerHTML = 'Ketik NIP untuk mengecek ketersediaan data karyawan.';
            nipFeedback.className = 'form-text mt-2 text-muted';
            submitBtn.disabled = true;
            return;
        }

        nipFeedback.innerHTML = '<span class="text-primary spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mencari NIP...';
        nipFeedback.className = 'form-text mt-2 text-primary';
        submitBtn.disabled = true;

        nipTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`/api/karyawan/nip/${nip}`);
                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    nipFeedback.innerHTML = `<i class="bi bi-check-circle-fill"></i> Ditemukan: <strong>${data.nama_karyawan}</strong> (${data.jabatan})`;
                    nipFeedback.className = 'form-text mt-2 text-success';
                    submitBtn.disabled = false;
                } else {
                    nipFeedback.innerHTML = `<i class="bi bi-x-circle-fill"></i> NIP tidak terdaftar di sistem.`;
                    nipFeedback.className = 'form-text mt-2 text-danger';
                }
            } catch (error) {
                nipFeedback.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i> Gagal mengecek NIP ke server.`;
                nipFeedback.className = 'form-text mt-2 text-danger';
            }
        }, 500); 
    });
</script>
@endsection