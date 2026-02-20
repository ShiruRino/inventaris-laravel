@extends('layouts.app')
@section('title', 'Data Barang')

@section('content')
    <style>
        .badge-kondisi { font-size: 0.8rem; text-transform: capitalize; }
    </style>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Master Data Barang</h3>
            <p class="text-muted">Manajemen seluruh daftar barang dan inventaris aset.</p>
        </div>
        <div>
            <a href="{{route('barang.printAll')}}" class="btn btn-danger"><i class="bi bi-printer me-1"></i> Cetak Semua</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                <i class="bi bi-plus-lg"></i> Tambah Barang Baru
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

    <form action="{{ route('barang.index') }}" method="GET" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari Barcode atau Nama..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="kategori" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <option value="Elektronik" {{ request('kategori') == 'Elektronik' ? 'selected' : '' }}>Elektronik</option>
                    <option value="Furnitur" {{ request('kategori') == 'Furnitur' ? 'selected' : '' }}>Furnitur</option>
                    <option value="Jaringan" {{ request('kategori') == 'Jaringan' ? 'selected' : '' }}>Jaringan</option>
                    <option value="Kendaraan" {{ request('kategori') == 'Kendaraan' ? 'selected' : '' }}>Kendaraan</option>
                    <option value="Peralatan Kantor" {{ request('kategori') == 'Peralatan Kantor' ? 'selected' : '' }}>Peralatan Kantor</option>
                    <option value="Lainnya" {{ request('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru Ditambahkan</option>
                    <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama Ditambahkan</option>
                    <option value="nama_asc" {{ request('sort') == 'nama_asc' ? 'selected' : '' }}>Nama Barang (A-Z)</option>
                    <option value="nama_desc" {{ request('sort') == 'nama_desc' ? 'selected' : '' }}>Nama Barang (Z-A)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
                <a href="{{ route('barang.index') }}" class="btn btn-light border"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Kode Barcode</th>
                            <th>Nama Barang</th>
                            <th>Kuantitas</th>
                            <th>Status Penguasaan</th>
                            <th>Kondisi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($barang as $i)
                            <tr>
                                <td class="ps-3 fw-bold">{{$i->kode_barcode}}</td>
                                <td>
                                    {{$i->nama_barang}}
                                    <span class="badge bg-light text-dark border ms-1">{{ $i->kategori }}</span>
                                    @if($i->foto_barang)
                                        <a href="{{ asset('storage/' . $i->foto_barang) }}" target="_blank" class="ms-1 text-primary" title="Lihat Foto"><i class="bi bi-image"></i></a>
                                    @endif
                                </td>
                                <td>{{$i->jumlah_barang}} Unit</td>
                                @php
                                    $statusKuasa = 'Personal';
                                    if($i->id_karyawan_pemegang == null && $i->lokasi_fisik !== null) $statusKuasa = 'Lokasi';

                                    $bgColor = 'bg-secondary';
                                    if($i->latestKondisi?->status_kondisi == 'Baik') $bgColor = 'bg-success';
                                    elseif($i->latestKondisi?->status_kondisi == 'Rusak Ringan') $bgColor = 'bg-warning text-dark';
                                    elseif($i->latestKondisi?->status_kondisi == 'Rusak Berat') $bgColor = 'bg-danger';
                                    elseif($i->latestKondisi?->status_kondisi == 'Hilang') $bgColor = 'bg-dark';
                                @endphp
                                <td>
                                    @if ($i->karyawan?->nama_karyawan)
                                    <i class="bi bi-person me-1"></i> 
                                    {{ $i->karyawan?->nama_karyawan }} 
                                    <small class="text-muted">({{ $statusKuasa }})</small>
                                    @else
                                    <i class="bi bi-geo-alt me-1"></i> 
                                    {{ $i->lokasi_fisik }} 
                                    <small class="text-muted">({{ $statusKuasa }})</small>
                                    @endif
                                </td>
                                <td><span class="badge {{$bgColor}} badge-kondisi">{{$i->latestKondisi->status_kondisi ?? 'Belum diperiksa'}}</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-info" onclick="fetchDetail({{ $i->id_barang }})" title="Lihat Detail"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="fetchEdit({{ $i->id_barang }})" title="Edit"><i class="bi bi-pencil"></i></button>
                                    
                                    <form action="{{ route('barang.destroy', $i->id_barang) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus barang ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{$barang->appends(request()->query())->links('pagination::bootstrap-5')}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahBarang" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrasi Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('barang.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Kontrak</label>
                                <select name="id_kontrak" class="form-select" required>
                                    <option value="">Pilih Kontrak...</option>
                                    @foreach($kontrak as $k)
                                        <option value="{{ $k->id_kontrak }}">{{ $k->no_kontrak }} - {{ $k->nama_vendor }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kode Barcode</label>
                                <input type="text" name="kode_barcode" class="form-control" placeholder="Contoh: INV-001" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Laptop Staff" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="">Pilih Kategori...</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Furnitur">Furnitur</option>
                                    <option value="Jaringan">Jaringan</option>
                                    <option value="Kendaraan">Kendaraan</option>
                                    <option value="Peralatan Kantor">Peralatan Kantor</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jumlah (QTY)</label>
                                <input type="number" name="jumlah_barang" class="form-control" value="1" min="1" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Spesifikasi Detail</label>
                                <textarea name="spesifikasi" class="form-control" rows="3" placeholder="Detail teknis barang"></textarea>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <label class="form-label d-block fw-bold">Penempatan Awal</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status_penguasaan" id="radioPersonal" value="personal" checked onchange="toggleInput('tambah')">
                                    <label class="form-check-label" for="radioPersonal">Aset Personal (Karyawan)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status_penguasaan" id="radioLokasi" value="lokasi" onchange="toggleInput('tambah')">
                                    <label class="form-check-label" for="radioLokasi">Aset Lokasi (Ruangan)</label>
                                </div>
                            </div>

                            <div class="col-md-12" id="inputPemegang_tambah">
                                <label class="form-label">NIP Karyawan Pemegang</label>
                                <input type="text" class="form-control" name="nip" id="nip_tambah" placeholder="Ketik NIP Karyawan..." onkeyup="cariKaryawan(this.value, 'tambah')">
                                <div id="karyawanResult_tambah" class="form-text text-muted mt-1">Masukkan minimal 3 digit NIP untuk mencari.</div>
                            </div>

                            <div class="col-md-12 d-none" id="inputLokasi_tambah">
                                <label class="form-label">Lokasi Fisik Ruangan</label>
                                <input type="text" name="lokasi_fisik" class="form-control" placeholder="Misal: R. Server Lt. 2">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Foto Barang (Opsional)</label>
                                <input type="file" name="foto_barang" class="form-control" accept="image/png, image/jpeg, image/jpg">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditBarang" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title">Edit Data Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditBarang" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="edit_id_barang"> 
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Kontrak</label>
                                <select name="id_kontrak" class="form-select" id="edit_kontrak" required>
                                    @foreach($kontrak as $k)
                                        <option value="{{ $k->id_kontrak }}">{{ $k->no_kontrak }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kode Barcode</label>
                                <input type="text" name="kode_barcode" class="form-control" id="edit_barcode" readonly> 
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" id="edit_nama" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select" id="edit_kategori" required>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Furnitur">Furnitur</option>
                                    <option value="Jaringan">Jaringan</option>
                                    <option value="Kendaraan">Kendaraan</option>
                                    <option value="Peralatan Kantor">Peralatan Kantor</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jumlah Barang</label>
                                <input type="number" name="jumlah_barang" class="form-control" id="edit_qty" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Spesifikasi</label>
                                <textarea name="spesifikasi" class="form-control" id="edit_spek" rows="3"></textarea>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold">Posisi Saat Ini</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status_penguasaan" id="editRadioPersonal" value="personal" onchange="toggleInput('edit')">
                                    <label class="form-check-label">Personal</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status_penguasaan" id="editRadioLokasi" value="lokasi" onchange="toggleInput('edit')">
                                    <label class="form-check-label">Lokasi</label>
                                </div>
                            </div>

                            <div class="col-md-12" id="inputPemegang_edit">
                                <label class="form-label">NIP Karyawan Pemegang</label>
                                <input type="text" name="nip" class="form-control" id="nip_edit" placeholder="Ketik NIP..." onkeyup="cariKaryawan(this.value, 'edit')">
                                <div id="karyawanResult_edit" class="form-text text-muted mt-1">Masukkan NIP untuk mencari.</div>
                            </div>

                            <div class="col-md-12 d-none" id="inputLokasi_edit">
                                <label class="form-label">Lokasi Fisik Ruangan</label>
                                <input type="text" name="lokasi_fisik" class="form-control" id="edit_lokasi_text">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Kondisi Terkini</label>
                                <select name="kondisi" class="form-select" id="edit_kondisi">
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                    <option value="Hilang">Hilang</option>
                                </select>
                            </div>
                            <div id="additional-fields-container" class="col-12"></div>

                            <div class="col-md-12">
                                <label class="form-label">Ganti Foto Barang (Opsional)</label>
                                <input type="file" name="foto_barang" class="form-control" accept="image/png, image/jpeg, image/jpg">
                            </div>
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

    <div class="modal fade" id="modalDetailBarang" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="detail_nama_header">Loading...</h5>
                        <small class="text-muted" id="detail_barcode_header">Loading...</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button">Informasi Umum</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="kontrak-tab" data-bs-toggle="tab" data-bs-target="#kontrak-pane" type="button">Detail Kontrak</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="kondisi-tab" data-bs-toggle="tab" data-bs-target="#kondisi-pane" type="button">Detail Kondisi</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="mobilisasi-tab" data-bs-toggle="tab" data-bs-target="#mobilisasi-pane" type="button">Riwayat Mobilisasi</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="info-pane">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="30%" class="fw-bold">Kategori</td>
                                    <td>: <span class="badge bg-secondary" id="detail_kategori">-</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Spesifikasi</td>
                                    <td>: <span id="detail_spek">-</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Kuantitas</td>
                                    <td>: <span id="detail_qty">0</span> Unit</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status Penguasaan</td>
                                    <td>: <span id="detail_posisi">-</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Kondisi Terkini</td>
                                    <td>: <span class="badge bg-success" id="detail_kondisi">Baik</span></td>
                                </tr>
                            </table>
                            <div class="text-center d-flex w-100 flex-column align-items-center mt-3 p-3 bg-light rounded">
                                <small>Barcode Preview:</small><br>
                                <div id="detail_qr_container" class="my-2"></div>
                                <span id="detail_barcode_img"></span>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="kontrak-pane">
                            <table class="table">
                                <tr>
                                    <th width="30%">Nomor SPK/Kontrak</th>
                                    <td><span id="detail_no_kontrak">-</span></td>
                                </tr>
                                <tr>
                                    <th>Tahun Pengadaan</th>
                                    <td><span id="detail_thn_kontrak">-</span></td>
                                </tr>
                                <tr>
                                    <th>Vendor / Supplier</th>
                                    <td><span id="detail_vendor">-</span></td>
                                </tr>
                                <tr>
                                    <th>Pihak Pengada</th>
                                    <td><span id="detail_pengada">-</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="kondisi-pane">
                            <div class="table-responsive mt-3">
                                <table class="table table-sm align-middle text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Waktu Cek</th>
                                            <th>Status Kondisi</th>
                                            <th>Catatan</th>
                                            <th>Operator</th>
                                            <th>Lampiran Foto</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail_kondisi_tbody">
                                        </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="mobilisasi-pane">
                            <div class="table-responsive mt-3">
                                <table class="table table-sm align-middle text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Aktivitas</th>
                                            <th>Asal</th>
                                            <th>Tujuan</th>
                                            <th>Operator</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail_mobilisasi_tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="btn_print_modal" target="_blank" class="btn btn-dark">
                        <i class="bi bi-printer me-1"></i> Cetak Label
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-warning" onclick="switchModalToEdit()">Edit Barang Ini</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentBarangId = null;

        function fetchDetail(id) {
            currentBarangId = id;
            new bootstrap.Modal(document.getElementById('modalDetailBarang')).show();
        
            fetch(`/barang/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('detail_qr_container').innerHTML = data.qr_html;
                    document.getElementById('detail_nama_header').innerText = data.nama_barang;
                    document.getElementById('detail_barcode_header').innerText = data.kode_barcode;
                    
                    document.getElementById('detail_kategori').innerText = data.kategori || 'Lainnya';
                    document.getElementById('detail_spek').innerText = data.spesifikasi || '-';
                    document.getElementById('detail_qty').innerText = data.jumlah_barang;
        
                    if(data.id_karyawan_pemegang) {
                        let pemegang = data.karyawan ? data.karyawan.nama_karyawan : 'Unknown';
                        document.getElementById('detail_posisi').innerText = "Personal (" + pemegang + ")";
                    } else {
                        document.getElementById('detail_posisi').innerText = "Lokasi: " + (data.lokasi_fisik || '-');
                    }
                    
                    document.getElementById('detail_barcode_img').innerText = data.kode_barcode;
        
                    if(data.kontrak) {
                        document.getElementById('detail_no_kontrak').innerText = data.kontrak.no_kontrak;
                        document.getElementById('detail_thn_kontrak').innerText = data.kontrak.tahun_kontrak;
                        document.getElementById('detail_vendor').innerText = data.kontrak.nama_vendor;
                        document.getElementById('detail_pengada').innerText = data.kontrak.pihak_pengada;
                    } else {
                        document.getElementById('detail_no_kontrak').innerText = '-';
                        document.getElementById('detail_thn_kontrak').innerText = '-';
                        document.getElementById('detail_vendor').innerText = '-';
                        document.getElementById('detail_pengada').innerText = '-';
                    }
        
                    let tbodyKondisi = document.getElementById('detail_kondisi_tbody');
                    tbodyKondisi.innerHTML = ''; 

                    if (data.kondisi && Array.isArray(data.kondisi) && data.kondisi.length > 0) {
                        data.kondisi.forEach(k => {
                            let statusBadge = '';
                            if (k.status_kondisi === 'Baik') statusBadge = '<span class="badge bg-success">Baik</span>';
                            else if (k.status_kondisi === 'Rusak Ringan') statusBadge = '<span class="badge bg-warning text-dark">Rusak Ringan</span>';
                            else if (k.status_kondisi === 'Rusak Berat') statusBadge = '<span class="badge bg-danger">Rusak Berat</span>';
                            else if (k.status_kondisi === 'Hilang') statusBadge = '<span class="badge bg-dark">Hilang</span>';
                            else statusBadge = `<span class="badge bg-secondary">${k.status_kondisi || '-'}</span>`;

                            let waktuCek = '-';
                            if (k.created_at) {
                                let d = new Date(k.created_at);
                                waktuCek = d.toLocaleDateString('id-ID', {
                                    day: '2-digit', month: 'short', year: 'numeric', 
                                    hour: '2-digit', minute: '2-digit'
                                });
                            }

                            let fotoBtn = k.foto_kondisi 
                                ? `<a href="/storage/${k.foto_kondisi}" target="_blank" class="btn btn-sm btn-outline-info" title="Lihat Foto"><i class="bi bi-image"></i></a>` 
                                : '<small class="text-muted">-</small>';

                            let tr = `
                                <tr>
                                    <td class="text-nowrap">${waktuCek}</td>
                                    <td>${statusBadge}</td>
                                    <td class="text-start fst-italic">${k.catatan || '-'}</td>
                                    <td class="text-start">${k.operator && k.operator.karyawan ? k.operator.karyawan.nama_karyawan : '-'}</td>
                                    <td>${fotoBtn}</td>
                                </tr>
                            `;
                            tbodyKondisi.innerHTML += tr;
                        });

                    } else {
                        tbodyKondisi.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                    Belum ada riwayat pemeriksaan kondisi.
                                </td>
                            </tr>
                        `;
                    }

                    let tbodyMobilisasi = document.getElementById('detail_mobilisasi_tbody');
                    tbodyMobilisasi.innerHTML = '';

                    if (data.mobilisasi && Array.isArray(data.mobilisasi) && data.mobilisasi.length > 0) {
                        data.mobilisasi.forEach(m => {
                            let waktuMob = '-';
                            if (m.created_at) {
                                let d = new Date(m.created_at);
                                waktuMob = d.toLocaleDateString('id-ID', {
                                    day: '2-digit', month: 'short', year: 'numeric',
                                    hour: '2-digit', minute: '2-digit'
                                });
                            }

                            let aktivitas = '<span class="badge bg-warning text-dark">Relokasi</span>';
                            let tujuan = m.lokasi_tujuan || '-';

                            if (m.asal === '(Vendor)') {
                                aktivitas = '<span class="badge bg-success">Registrasi Awal</span>';
                            } else if (m.id_penerima) {
                                aktivitas = '<span class="badge bg-primary">Handover</span>';
                                tujuan = m.penerima && m.penerima.nama_karyawan ? m.penerima.nama_karyawan : `NIP: ${m.id_penerima}`;
                            }

                            let operatorName = m.operator && m.operator.karyawan ? m.operator.karyawan.nama_karyawan : '-';

                            let tr = `
                                <tr>
                                    <td class="text-nowrap">${waktuMob}</td>
                                    <td>${aktivitas}</td>
                                    <td>${m.asal || '-'}</td>
                                    <td>${tujuan}</td>
                                    <td>${operatorName}</td>
                                </tr>
                            `;
                            tbodyMobilisasi.innerHTML += tr;
                        });
                    } else {
                        tbodyMobilisasi.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-arrow-left-right fs-4 d-block mb-1"></i>
                                    Belum ada riwayat mobilisasi.
                                </td>
                            </tr>
                        `;
                    }
        
                    const printBtn = document.getElementById('btn_print_modal');
                    printBtn.href = `/barang/${data.id_barang}/print`;
                    document.getElementById('edit_id_barang').value = data.id_barang;
                })
                .catch(err => console.error(err));
        }
        
        const conditionSelect = document.getElementById('edit_kondisi');
        const container = document.getElementById('additional-fields-container');

        conditionSelect.addEventListener('change', function() {
            container.innerHTML = '';
            if (this.value !== '') {
                const newHtml = `
                    <div class="added-content col-12 mt-2">
                        <label class="form-label">Catatan Kondisi</label>
                        <input type="text" class="form-control" name="catatan" placeholder="Contoh: Lecet pemakaian"/>
                    </div>`;
                container.innerHTML = newHtml;
            }
        });

        function fetchEdit(id) {
            currentBarangId = id;
            
            const detailEl = document.getElementById('modalDetailBarang');
            const detailModal = bootstrap.Modal.getInstance(detailEl);
            if(detailModal) detailModal.hide();

            fetch(`/barang/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('edit_id_barang').value = data.id_barang;
                    document.getElementById('edit_kontrak').value = data.id_kontrak;
                    document.getElementById('edit_barcode').value = data.kode_barcode;
                    document.getElementById('edit_nama').value = data.nama_barang;
                    document.getElementById('edit_qty').value = data.jumlah_barang;
                    document.getElementById('edit_kategori').value = data.kategori || 'Lainnya';
                    document.getElementById('edit_spek').value = data.spesifikasi || '';
                    
                    if (data.latest_kondisi && data.latest_kondisi.status_kondisi) {
                        document.getElementById('edit_kondisi').value = data.latest_kondisi.status_kondisi;
                    }

                    if(data.id_karyawan_pemegang) {
                        document.getElementById('editRadioPersonal').checked = true;
                        
                        if(data.karyawan) {
                            document.getElementById('nip_edit').value = data.karyawan.nip || '';
                            document.getElementById('karyawanResult_edit').innerHTML = `<i class="bi bi-check-circle-fill"></i> Data saat ini: <strong>${data.karyawan.nama_karyawan}</strong>`;
                            document.getElementById('karyawanResult_edit').className = 'form-text text-success mt-1';
                        }
                        
                        document.getElementById('edit_lokasi_text').value = '';
                    } else {
                        document.getElementById('editRadioLokasi').checked = true;
                        document.getElementById('nip_edit').value = '';
                        document.getElementById('karyawanResult_edit').innerHTML = 'Masukkan NIP untuk mencari.';
                        document.getElementById('karyawanResult_edit').className = 'form-text text-muted mt-1';
                        
                        document.getElementById('edit_lokasi_text').value = data.lokasi_fisik || '';
                    }
                    
                    toggleInput('edit'); 

                    let baseUrl = "{{ route('barang.update', ':id') }}";
                    let updateUrl = baseUrl.replace(':id', data.id_barang);
                    document.getElementById('formEditBarang').action = updateUrl;

                    new bootstrap.Modal(document.getElementById('modalEditBarang')).show();
                })
                .catch(err => console.error(err));
        }

        function switchModalToEdit() {
            if(currentBarangId) fetchEdit(currentBarangId);
        }

        function toggleInput(mode) {
            let isPersonal, inputPemegang, inputLokasi;

            if (mode === 'tambah') {
                isPersonal = document.getElementById('radioPersonal').checked;
                inputPemegang = document.getElementById('inputPemegang_tambah');
                inputLokasi = document.getElementById('inputLokasi_tambah');
            } else {
                isPersonal = document.getElementById('editRadioPersonal').checked;
                inputPemegang = document.getElementById('inputPemegang_edit');
                inputLokasi = document.getElementById('inputLokasi_edit');
            }

            if (isPersonal) {
                inputPemegang.classList.remove('d-none');
                inputLokasi.classList.add('d-none');
            } else {
                inputPemegang.classList.add('d-none');
                inputLokasi.classList.remove('d-none');
            }
        }

        let typingTimer;
        const doneTypingInterval = 500; 

        function cariKaryawan(nip, mode) {
            clearTimeout(typingTimer);
            const resultDiv = document.getElementById(`karyawanResult_${mode}`);

            if (nip.length < 3) {
                resultDiv.innerHTML = 'Masukkan minimal 3 digit NIP untuk mencari.';
                resultDiv.className = 'form-text text-muted mt-1';
                return;
            }

            resultDiv.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Mencari data...';
            resultDiv.className = 'form-text text-primary mt-1';

            typingTimer = setTimeout(() => {
                fetch(`/api/karyawan/nip/${nip}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            resultDiv.innerHTML = `<i class="bi bi-check-circle-fill"></i> Ditemukan: <strong>${data.nama_karyawan}</strong> (${data.jabatan})`;
                            resultDiv.className = 'form-text text-success mt-1';
                        } else {
                            resultDiv.innerHTML = '<i class="bi bi-x-circle-fill"></i> NIP tidak ditemukan.';
                            resultDiv.className = 'form-text text-danger mt-1';
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Terjadi kesalahan jaringan.';
                        resultDiv.className = 'form-text text-danger mt-1';
                    });
            }, doneTypingInterval);
        }
    </script>
@endsection