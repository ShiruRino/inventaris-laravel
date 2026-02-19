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
                                <td>{{$i->nama_barang}}</td>
                                <td>{{$i->jumlah_barang}} Unit</td>
                                @php
                                    $statusKuasa = 'Personal';
                                    if($i->id_karyawan == null && $i->lokasi_fisik !== null) $statusKuasa = 'Lokasi';

                                    $bgColor = 'bg-secondary';
                                    if($i->kondisi == 'Baik') $bgColor = 'bg-success';
                                    elseif($i->kondisi == 'Rusak Ringan') $bgColor = 'bg-warning text-dark';
                                    elseif($i->kondisi == 'Rusak Berat') $bgColor = 'bg-danger';
                                    elseif($i->kondisi == 'Hilang') $bgColor = 'bg-dark';
                                @endphp
                                <td>
                                    <i class="bi bi-person me-1"></i> 
                                    {{ $i->karyawan->nama_karyawan ?? '-'}} 
                                    <small class="text-muted">({{ $statusKuasa }})</small>
                                </td>
                                <td><span class="badge {{$bgColor}} badge-kondisi">{{$i->kondisi ?? 'Belum diperiksa'}}</span></td>
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
                    {{$barang->links('pagination::bootstrap-5')}}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="modalTambahBarang" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrasi Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('barang.store') }}" method="POST">
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
                            <div class="col-md-8">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Laptop Staff" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jumlah Barang (QTY)</label>
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
                                <label class="form-label">Pilih Karyawan Pemegang</label>
                                <select name="id_karyawan_pemegang" class="form-select">
                                    <option value="">Pilih Nama Karyawan...</option>
                                    @foreach($karyawan as $kry)
                                        <option value="{{ $kry->id_karyawan }}">{{ $kry->nama_karyawan }} - {{ $kry->jabatan }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 d-none" id="inputLokasi_tambah">
                                <label class="form-label">Lokasi Fisik Ruangan</label>
                                <input type="text" name="lokasi_fisik" class="form-control" placeholder="Misal: R. Server Lt. 2">
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

    {{-- MODAL EDIT --}}
    <div class="modal fade" id="modalEditBarang" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title">Edit Data Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditBarang" method="POST">
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
                            <div class="col-md-8">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="nama_barang" class="form-control" id="edit_nama" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jumlah Barang</label>
                                <input type="number" name="jumlah_barang" class="form-control" id="edit_qty" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Spesifikasi</label>
                                <textarea name="spesifikasi" class="form-control" id="edit_spek" rows="3"></textarea>
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
                                <select name="id_karyawan" class="form-select" id="edit_pemegang">
                                    <option value="">Pilih Karyawan...</option>
                                    @foreach($karyawan as $kry)
                                        <option value="{{ $kry->id_karyawan }}">{{ $kry->nama_karyawan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 d-none" id="inputLokasi_edit">
                                <input type="text" name="lokasi_fisik" class="form-control" id="edit_lokasi_text">
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

    {{-- MODAL DETAIL --}}
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
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        
                        <div class="tab-pane fade show active" id="info-pane">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="30%" class="fw-bold">Spesifikasi</td>
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

        // --- 1. FETCH DETAIL ---
        function fetchDetail(id) {
            currentBarangId = id;
            new bootstrap.Modal(document.getElementById('modalDetailBarang')).show();

            fetch(`/barang/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('detail_qr_container').innerHTML = data.qr_html;
                    // Header
                    document.getElementById('detail_nama_header').innerText = data.nama_barang;
                    document.getElementById('detail_barcode_header').innerText = data.kode_barcode;
                    
                    // Tab Info
                    document.getElementById('detail_spek').innerText = data.spesifikasi || '-';
                    document.getElementById('detail_qty').innerText = data.jumlah_barang;
                    document.getElementById('detail_kondisi').innerText = data.kondisi;
                    
                    // Logic Posisi
                    if(data.id_karyawan_pemegang) {
                        let pemegang = data.karyawan ? data.karyawan.nama_karyawan : 'Unknown';
                        document.getElementById('detail_posisi').innerText = "Personal (" + pemegang + ")";
                    } else {
                        document.getElementById('detail_posisi').innerText = "Lokasi: " + (data.lokasi_fisik || '-');
                    }
                    
                    document.getElementById('detail_barcode_img').innerText = data.kode_barcode;

                    // Tab Kontrak
                    if(data.kontrak) {
                        document.getElementById('detail_no_kontrak').innerText = data.kontrak.no_kontrak;
                        document.getElementById('detail_thn_kontrak').innerText = data.kontrak.tahun_kontrak;
                        document.getElementById('detail_vendor').innerText = data.kontrak.nama_vendor;
                        document.getElementById('detail_pengada').innerText = data.kontrak.pihak_pengada;
                    } else {
                        document.getElementById('detail_no_kontrak').innerText = '-';
                    }
                    const printBtn = document.getElementById('btn_print_modal');
                    printBtn.href = `/barang/${data.id_barang}/print`;
                    // Store ID for edit switch
                    document.getElementById('edit_id_barang').value = data.id_barang;
                })
                .catch(err => console.error(err));
        }

        // --- 2. FETCH EDIT ---
        function fetchEdit(id) {
            currentBarangId = id;
            
            // Close detail modal
            const detailEl = document.getElementById('modalDetailBarang');
            const detailModal = bootstrap.Modal.getInstance(detailEl);
            if(detailModal) detailModal.hide();

            fetch(`/barang/${id}`)
                .then(res => res.json())
                .then(data => {
                    // Fill inputs
                    document.getElementById('edit_id_barang').value = data.id_barang;
                    document.getElementById('edit_kontrak').value = data.id_kontrak;
                    document.getElementById('edit_barcode').value = data.kode_barcode;
                    document.getElementById('edit_nama').value = data.nama_barang;
                    document.getElementById('edit_qty').value = data.jumlah_barang;
                    document.getElementById('edit_spek').value = data.spesifikasi;
                    document.getElementById('edit_kondisi').value = data.kondisi;

                    // Logic Radio Button
                    if(data.id_karyawan) {
                        document.getElementById('editRadioPersonal').checked = true;
                        document.getElementById('edit_pemegang').value = data.id_karyawan;
                        document.getElementById('edit_lokasi_text').value = '';
                    } else {
                        document.getElementById('editRadioLokasi').checked = true;
                        document.getElementById('edit_pemegang').value = '';
                        document.getElementById('edit_lokasi_text').value = data.lokasi_fisik;
                    }
                    
                    toggleInput('edit'); // Refresh UI display

                    // Update Form Action
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

        // --- 3. TOGGLE INPUT (Radio Logic) ---
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
    </script>
@endsection