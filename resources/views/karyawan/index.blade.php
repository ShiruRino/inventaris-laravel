@extends('layouts.app') 

@section('title', 'Data Karyawan')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3>Master Data Karyawan</h3>
        <p class="text-muted">Database personil perusahaan penerima fasilitas aset.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKaryawan">
        <i class="bi bi-person-plus"></i> Tambah Karyawan
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

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle p-3 me-3">
                    <i class="bi bi-people-fill h4 mb-0"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0">Total Pegawai</h6>
                    <h4 class="mb-0">{{ $karyawan->total() }} Orang</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('karyawan.index') }}" method="GET" class="mb-3">
    <div class="row g-2">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari NIP atau Nama..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="divisi" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Departemen</option>
                <option value="IT" {{ request('divisi') == 'IT' ? 'selected' : '' }}>Teknologi Informasi</option>
                <option value="HR" {{ request('divisi') == 'HR' ? 'selected' : '' }}>Human Resource</option>
                <option value="GA" {{ request('divisi') == 'GA' ? 'selected' : '' }}>General Affair</option>
                <option value="FIN" {{ request('divisi') == 'FIN' ? 'selected' : '' }}>Finance</option>
                <option value="OPS" {{ request('divisi') == 'OPS' ? 'selected' : '' }}>Operasional</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="sort" class="form-select" onchange="this.form.submit()">
                <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
                <option value="nama_asc" {{ request('sort') == 'nama_asc' ? 'selected' : '' }}>Nama (A-Z)</option>
                <option value="nama_desc" {{ request('sort') == 'nama_desc' ? 'selected' : '' }}>Nama (Z-A)</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
            <a href="{{ route('karyawan.index') }}" class="btn btn-light border"><i class="bi bi-arrow-clockwise"></i></a>
        </div>
    </div>
</form>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">NIP</th>
                        <th>Nama Lengkap</th>
                        <th>Jabatan / Divisi</th>
                        <th>Kontak</th>
                        <th>Tanggungan Aset</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($karyawan as $i)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $i->nip }}</td>
                            <td>{{ $i->nama_karyawan }}</td>
                            <td>
                                {{ $i->jabatan }} 
                                <span class="badge bg-secondary ms-1">{{ $i->divisi }}</span>
                            </td>
                            <td>{{ $i->kontak ?? '-' }}</td>
                            <td>
                                @php
                                    $count = $i->barang_count;
                                    $bg = $count > 0 ? 'bg-primary' : 'bg-secondary';
                                    if($count > 2) $bg = 'bg-warning text-dark';
                                @endphp
                                <span class="badge {{ $bg }}">{{ $count }} Barang</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-info" onclick="fetchDetail({{ $i->id_karyawan }})" title="Lihat Aset">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="fetchEdit({{ $i->id_karyawan }})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <form action="{{route('karyawan.destroy', $i->id_karyawan)}}" method="POST" class="d-inline" onsubmit="return confirmDelete(this, {{ $count }})">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data karyawan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $karyawan->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahKaryawan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Data Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{route('karyawan.store')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">NIP</label>
                            <input type="text" placeholder="Contoh: 2024001" name="nip" class="form-control" value="{{old('nip')}}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kontak (No. Telepon / E-mail)</label>
                            <input type="text" placeholder="Contoh: 081937361264" name="kontak" class="form-control" value="{{old('kontak')}}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" placeholder="Contoh: Budi Santoso" name="nama_karyawan" class="form-control" value="{{old('nama_karyawan')}}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" placeholder="Contoh: Staff Lapangan" name="jabatan" class="form-control" value="{{old('jabatan')}}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Departemen</label>
                        <select name="divisi" class="form-select" required>
                            <option value="">Pilih Departemen...</option>
                            <option value="IT" {{ old('divisi') == 'IT' ? 'selected' : '' }}>Teknologi Informasi</option>
                            <option value="HR" {{ old('divisi') == 'HR' ? 'selected' : '' }}>Human Resource</option>
                            <option value="GA" {{ old('divisi') == 'GA' ? 'selected' : '' }}>General Affair</option>
                            <option value="FIN" {{ old('divisi') == 'FIN' ? 'selected' : '' }}>Finance</option>
                            <option value="OPS" {{ old('divisi') == 'OPS' ? 'selected' : '' }}>Operasional</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditKaryawan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title">Edit Data Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditKaryawan" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_id_karyawan">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">NIP (Readonly)</label>
                            <input type="text" name="nip" class="form-control bg-light" id="edit_nip" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kontak (No. Telepon / E-mail)</label>
                            <input type="text" name="kontak" class="form-control" id="edit_telp">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_karyawan" class="form-control" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" id="edit_jabatan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Departemen</label>
                        <select name="divisi" class="form-select" id="edit_dept">
                            <option value="IT">Teknologi Informasi</option>
                            <option value="HR">Human Resource</option>
                            <option value="GA">General Affair</option>
                            <option value="FIN">Finance</option>
                            <option value="OPS">Operasional</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailKaryawan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="detail_header_nama">Loading...</h5>
                    <small class="text-muted" id="detail_header_jabatan">Loading...</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4 border-bottom pb-3">
                    <div class="col-md-4">
                        <span class="text-muted small d-block">NIP</span>
                        <span class="fw-bold" id="detail_nip">-</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">Departemen</span>
                        <span class="badge bg-secondary" id="detail_dept">-</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">Kontak</span>
                        <span class="fw-bold" id="detail_telp">-</span>
                    </div>
                </div>

                <h6 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>Daftar Aset</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Barcode</th>
                                <th>Nama Barang</th>
                                <th>Kondisi</th>
                                <th>Tgl Terima</th>
                            </tr>
                        </thead>
                        <tbody id="list_aset_karyawan">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning" onclick="switchModalToEdit()">Edit Profil</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let currentEmployeeId = null;

    function fetchDetail(id) {
        currentEmployeeId = id;
        
        document.getElementById('list_aset_karyawan').innerHTML = '<tr><td colspan="4" class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Memuat data...</div></td></tr>';
        document.getElementById('detail_header_nama').innerText = "Loading...";
        
        const modal = new bootstrap.Modal(document.getElementById('modalDetailKaryawan'));
        modal.show();

        fetch(`/api/karyawan/id/${id}`)
            .then(response => {
                if (!response.ok) throw new Error("Gagal mengambil data");
                return response.json();
            })
            .then(data => {
                document.getElementById('detail_header_nama').innerText = data.nama_karyawan;
                document.getElementById('detail_header_jabatan').innerText = data.jabatan;
                document.getElementById('detail_nip').innerText = data.nip;
                document.getElementById('detail_dept').innerText = data.divisi;
                document.getElementById('detail_telp').innerText = data.kontak || '-';

                const tbody = document.getElementById('list_aset_karyawan');
                tbody.innerHTML = ''; 

                if (data.barang && data.barang.length > 0) {
                    data.barang.forEach(asset => {
                        let kondisiText = asset.latest_kondisi ? asset.latest_kondisi.status_kondisi : 'Belum Dicek';
                        let badgeClass = 'bg-secondary';
                        
                        if(kondisiText === 'Baik') badgeClass = 'bg-success';
                        if(kondisiText === 'Rusak Ringan') badgeClass = 'bg-warning text-dark';
                        if(kondisiText === 'Rusak Berat') badgeClass = 'bg-danger';
                        if(kondisiText === 'Hilang') badgeClass = 'bg-dark';
                        
                        let dateReceived = asset.created_at ? new Date(asset.created_at).toLocaleDateString('id-ID') : '-';

                        tbody.innerHTML += `
                            <tr>
                                <td class="font-monospace fw-bold">${asset.kode_barcode}</td>
                                <td>${asset.nama_barang}</td>
                                <td><span class="badge ${badgeClass}">${kondisiText}</span></td>
                                <td>${dateReceived}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted fst-italic py-3">Tidak ada aset yang sedang dipegang.</td></tr>`;
                }
            })
            .catch(err => {
                alert("Terjadi kesalahan saat mengambil data karyawan.");
            });
    }

    function fetchEdit(id) {
        currentEmployeeId = id;

        const detailEl = document.getElementById('modalDetailKaryawan');
        const detailModal = bootstrap.Modal.getInstance(detailEl);
        if (detailModal) detailModal.hide();

        fetch(`/api/karyawan/id/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('edit_id_karyawan').value = data.id_karyawan; 
                document.getElementById('edit_nip').value = data.nip;
                document.getElementById('edit_nama').value = data.nama_karyawan;
                document.getElementById('edit_jabatan').value = data.jabatan;
                document.getElementById('edit_telp').value = data.kontak || '';
                document.getElementById('edit_dept').value = data.divisi;

                let baseUrl = "{{ route('karyawan.update', ':id') }}";
                let updateUrl = baseUrl.replace(':id', data.id_karyawan);
                
                document.getElementById('formEditKaryawan').action = updateUrl;

                new bootstrap.Modal(document.getElementById('modalEditKaryawan')).show();
            })
            .catch(error => {
                alert('Gagal memuat data untuk edit.');
            });
    }

    function switchModalToEdit() {
        if(currentEmployeeId) fetchEdit(currentEmployeeId);
    }

    function confirmDelete(form, assetCount) {
        if (assetCount > 0) {
            alert(`GAGAL: Karyawan ini masih memegang ${assetCount} aset.\nHarap lakukan pengembalian aset sebelum menghapus data karyawan.`);
            return false; 
        }
        return confirm('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.');
    }
</script>
@endsection