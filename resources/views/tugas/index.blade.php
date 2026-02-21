@extends('layouts.app')
@section('title', 'Manajemen Tugas Lapangan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Penugasan Tim Lapangan</h3>
            <p class="text-muted">Jadwalkan dan pantau tugas operasional lapangan (Maintenance, Cek Aset, dll).</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahTugas">
                <i class="bi bi-plus-lg"></i> Buat Tugas Baru
            </button>
        </div>
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

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('tugas.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Filter Petugas</label>
                    <select name="id_user_petugas" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Petugas</option>
                        @foreach($petugas as $p)
                            <option value="{{ $p->id_user }}" {{ request('id_user_petugas') == $p->id_user ? 'selected' : '' }}>
                                {{ $p->karyawan->nama_karyawan ?? $p->username }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Status Pelaksanaan</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="Belum Dibaca" {{ request('status') == 'Belum Dibaca' ? 'selected' : '' }}>Belum Dibaca</option>
                        <option value="Sudah Dibaca" {{ request('status') == 'Sudah Dibaca' ? 'selected' : '' }}>Sudah Dibaca</option>
                        <option value="Proses" {{ request('status') == 'Proses' ? 'selected' : '' }}>Proses</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Filter Tanggal Aktif</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <a href="{{ route('tugas.index') }}" class="btn btn-light border w-100"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Jadwal Tugas</th>
                            <th>Jenis & Target Aset</th>
                            <th>Ditugaskan Kepada</th>
                            <th>Status Pelaksanaan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tugas as $t)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">{{ $t->jadwal_mulai->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $t->jadwal_mulai->format('H:i') }} - {{ $t->jadwal_tenggat->format('H:i') }}</small>
                                </td>
                                <td>
                                    <span class="d-block fw-bold text-primary">{{ $t->jenis_tugas }}</span>
                                    @if($t->barang)
                                        <small class="text-muted"><i class="bi bi-box me-1"></i> {{ $t->barang->kode_barcode }} ({{ $t->barang->nama_barang }})</small>
                                    @else
                                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Tugas Umum / Non-Aset</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $t->petugas->karyawan->nama_karyawan ?? $t->petugas->username }}</span>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        if($t->status == 'Sudah Dibaca') $badgeClass = 'bg-info text-dark';
                                        if($t->status == 'Proses') $badgeClass = 'bg-warning text-dark';
                                        if($t->status == 'Selesai') $badgeClass = 'bg-success';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $t->status }}</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-info" onclick="fetchDetail({{ $t->id_tugas }})" title="Lihat Detail & Laporan"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="fetchEdit({{ $t->id_tugas }})" title="Edit Tugas"><i class="bi bi-pencil"></i></button>
                                    <form action="{{ route('tugas.destroy', $t->id_tugas) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus tugas ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                                    Belum ada data tugas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-center">
                    {{ $tugas->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahTugas" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Jadwal Penugasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('tugas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pilih Petugas (Tim Lapangan) <span class="text-danger">*</span></label>
                                <select name="id_user_petugas" class="form-select" required>
                                    <option value="">-- Pilih Petugas --</option>
                                    @foreach($petugas as $p)
                                        <option value="{{ $p->id_user }}">{{ $p->karyawan->nama_karyawan ?? $p->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Target Aset (Opsional)</label>
                                <select name="id_barang" class="form-select">
                                    <option value="">-- Bukan Tugas Spesifik Aset --</option>
                                    @foreach($barang as $b)
                                        <option value="{{ $b->id_barang }}">{{ $b->kode_barcode }} - {{ $b->nama_barang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Jenis Tugas <span class="text-danger">*</span></label>
                                <input type="text" name="jenis_tugas" class="form-control" placeholder="Contoh: Cek Kondisi Berkala, Relokasi Aset, Pemasangan Jaringan" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="jadwal_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tenggat Waktu (Deadline) <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="jadwal_tenggat" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Instruksi / Deskripsi Detail</label>
                                <textarea name="deskripsi_tugas" class="form-control" rows="3" placeholder="Tuliskan instruksi lengkap untuk petugas di lapangan..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Penugasan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditTugas" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title">Edit Penugasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditTugas" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Petugas Pelaksana</label>
                                <select name="id_user_petugas" id="edit_petugas" class="form-select" required>
                                    @foreach($petugas as $p)
                                        <option value="{{ $p->id_user }}">{{ $p->karyawan->nama_karyawan ?? $p->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Target Aset</label>
                                <select name="id_barang" id="edit_barang" class="form-select">
                                    <option value="">-- Bukan Tugas Spesifik Aset --</option>
                                    @foreach($barang as $b)
                                        <option value="{{ $b->id_barang }}">{{ $b->kode_barcode }} - {{ $b->nama_barang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Jenis Tugas</label>
                                <input type="text" name="jenis_tugas" id="edit_jenis" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status Pelaksanaan</label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="Belum Dibaca">Belum Dibaca</option>
                                    <option value="Sudah Dibaca">Sudah Dibaca</option>
                                    <option value="Proses">Proses</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Waktu Mulai</label>
                                <input type="datetime-local" name="jadwal_mulai" id="edit_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tenggat Waktu</label>
                                <input type="datetime-local" name="jadwal_tenggat" id="edit_tenggat" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Instruksi</label>
                                <textarea name="deskripsi_tugas" id="edit_deskripsi" class="form-control" rows="3"></textarea>
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

    <div class="modal fade" id="modalDetailTugas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detail_title">Detail Laporan Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td width="35%" class="text-muted">Jenis Tugas</td>
                            <td class="fw-bold" id="detail_jenis"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Petugas</td>
                            <td id="detail_petugas"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Target Aset</td>
                            <td id="detail_aset"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jadwal</td>
                            <td id="detail_waktu"></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td id="detail_status"></td>
                        </tr>
                    </table>
                    <hr>
                    <h6 class="fw-bold"><i class="bi bi-card-text"></i> Instruksi Admin</h6>
                    <p class="small bg-light p-2 rounded border" id="detail_instruksi"></p>

                    <h6 class="fw-bold mt-3"><i class="bi bi-reply-all-fill"></i> Laporan dari Petugas</h6>
                    <p class="small bg-light p-2 rounded border" id="detail_catatan_petugas"></p>
                    
                    <div id="detail_foto_container" class="mt-3">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function formatDateTimeLocal(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
            return date.toISOString().slice(0, 16);
        }

        function fetchEdit(id) {
            fetch(`/tugas/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('edit_petugas').value = data.id_user_petugas;
                    document.getElementById('edit_barang').value = data.id_barang || '';
                    document.getElementById('edit_jenis').value = data.jenis_tugas;
                    document.getElementById('edit_status').value = data.status;
                    document.getElementById('edit_deskripsi').value = data.deskripsi_tugas || '';
                    
                    document.getElementById('edit_mulai').value = formatDateTimeLocal(data.jadwal_mulai);
                    document.getElementById('edit_tenggat').value = formatDateTimeLocal(data.jadwal_tenggat);
                    
                    document.getElementById('formEditTugas').action = `/tugas/${id}`;
                    new bootstrap.Modal(document.getElementById('modalEditTugas')).show();
                });
        }

        function fetchDetail(id) {
            fetch(`/tugas/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('detail_jenis').innerText = data.jenis_tugas;
                    document.getElementById('detail_petugas').innerText = data.petugas.karyawan ? data.petugas.karyawan.nama_karyawan : data.petugas.username;
                    document.getElementById('detail_aset').innerText = data.barang ? `${data.barang.kode_barcode} (${data.barang.nama_barang})` : 'Tugas Umum (Non-Aset)';
                    
                    const dMulai = new Date(data.jadwal_mulai).toLocaleString('id-ID');
                    const dTenggat = new Date(data.jadwal_tenggat).toLocaleString('id-ID');
                    document.getElementById('detail_waktu').innerText = `${dMulai} s/d ${dTenggat}`;
                    
                    let badgeColor = 'bg-secondary';
                    if(data.status === 'Sudah Dibaca') badgeColor = 'bg-info text-dark';
                    if(data.status === 'Proses') badgeColor = 'bg-warning text-dark';
                    if(data.status === 'Selesai') badgeColor = 'bg-success';
                    document.getElementById('detail_status').innerHTML = `<span class="badge ${badgeColor}">${data.status}</span>`;

                    document.getElementById('detail_instruksi').innerText = data.deskripsi_tugas || 'Tidak ada instruksi khusus.';
                    document.getElementById('detail_catatan_petugas').innerText = data.catatan_petugas || 'Petugas belum memberikan catatan laporan.';

                    let fotoHtml = '';
                    if (data.foto_bukti_tugas && data.foto_bukti_tugas.length > 0) {
                        fotoHtml = '<h6 class="fw-bold mt-2"><i class="bi bi-images"></i> Bukti Foto</h6><div class="d-flex flex-wrap gap-2">';
                        data.foto_bukti_tugas.forEach(foto => {
                            fotoHtml += `<a href="/storage/${foto}" target="_blank"><img src="/storage/${foto}" class="img-thumbnail" style="width:100px; height:100px; object-fit:cover;"></a>`;
                        });
                        fotoHtml += '</div>';
                    }
                    document.getElementById('detail_foto_container').innerHTML = fotoHtml;

                    new bootstrap.Modal(document.getElementById('modalDetailTugas')).show();
                });
        }
    </script>
@endsection