@extends('layouts.app')
@section('title', 'Data Kontrak')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3>Master Data Kontrak</h3>
        <p class="text-muted">Data referensi pengadaan barang dan vendor.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKontrak">
        <i class="bi bi-plus-lg"></i> Tambah Kontrak
    </button>
</div>

<div class="alert alert-info d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-info-circle-fill me-2"></i>
    <div>
        <strong>Catatan:</strong> Data kontrak digunakan sebagai referensi saat input barang. Pastikan Nomor Kontrak diisi sesuai dokumen SPK asli.
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Kontrak / SPK</th>
                        <th>Tahun</th>
                        <th>Nama Vendor</th>
                        <th>Pihak Pengada</th>
                        <th>Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kontrak as $i)
                    <tr>
                        <td class="ps-3 fw-bold">{{$i->no_kontrak}}</td>
                        <td><span class="badge bg-primary">{{$i->tahun_kontrak}}</span></td>
                        <td>{{$i->nama_vendor}}</td>
                        <td>{{$i->pihak_pengada}}</td>
                        <td class="text-muted small">{{Str::limit($i->keterangan, 50) ?? 'Tidak ada keterangan.'}}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info" onclick="fetchDetail({{ $i->id_kontrak }})" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="fetchEdit({{ $i->id_kontrak }})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            
                            {{-- Delete Form --}}
                            <form action="{{ route('kontrak.destroy', $i->id_kontrak) }}" method="POST" class="d-inline" onsubmit="return confirmDelete(this)">
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
                            Belum ada data kontrak.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{$kontrak->links('pagination::bootstrap-5')}}
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahKontrak" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Data Kontrak Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('kontrak.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nomor Kontrak / SPK <span class="text-danger">*</span></label>
                        <input type="text" name="no_kontrak" class="form-control" placeholder="Contoh: SPK/IT/001/2024" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Nama Vendor <span class="text-danger">*</span></label>
                            <input type="text" name="nama_vendor" class="form-control" placeholder="PT. Nama Vendor" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tahun</label>
                            <input type="number" name="tahun_kontrak" class="form-control" value="{{ date('Y') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pihak Pengada</label>
                        <input type="text" name="pihak_pengada" class="form-control" placeholder="Divisi IT / GA" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan Proyek</label>
                        <textarea name="keterangan" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kontrak</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="modalEditKontrak" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle">
                <h5 class="modal-title">Edit Data Kontrak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditKontrak" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_id_kontrak">
                    
                    <div class="mb-3">
                        <label class="form-label">Nomor Kontrak</label>
                        <input type="text" name="no_kontrak" class="form-control" id="edit_no_kontrak" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Nama Vendor</label>
                            <input type="text" name="nama_vendor" class="form-control" id="edit_vendor" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tahun</label>
                            <input type="number" name="tahun_kontrak" class="form-control" id="edit_tahun" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pihak Pengada</label>
                        <input type="text" name="pihak_pengada" class="form-control" id="edit_pengada" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" id="edit_ket" rows="3"></textarea>
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
<div class="modal fade" id="modalDetailKontrak" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Detail Kontrak</h5>
                    <small class="text-muted" id="detail_no_header">Loading...</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="35%" class="text-secondary">Vendor</td>
                                <td class="fw-bold" id="detail_vendor">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Tahun</td>
                                <td class="fw-bold"><span class="badge bg-primary" id="detail_tahun">-</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="35%" class="text-secondary">Pengada</td>
                                <td class="fw-bold" id="detail_pengada">-</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Ket</td>
                                <td class="fst-italic" id="detail_ket">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h6 class="border-bottom pb-2 mb-3">Daftar Aset dalam Kontrak Ini</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Barcode</th>
                                <th>Nama Barang</th>
                                <th>Kondisi</th>
                            </tr>
                        </thead>
                        <tbody id="detail_list_barang">
                            {{-- JS will populate this --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning" onclick="switchModalToEdit()">Edit Kontrak</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let currentKontrakId = null;

    // --- 1. FETCH DETAIL ---
    function fetchDetail(id) {
        currentKontrakId = id;
        
        // Show Loading State
        document.getElementById('detail_list_barang').innerHTML = '<tr><td colspan="3" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</td></tr>';
        
        // Show Modal
        new bootstrap.Modal(document.getElementById('modalDetailKontrak')).show();

        fetch(`/kontrak/${id}`)
            .then(res => res.json())
            .then(data => {
                // Populate Header Info
                document.getElementById('detail_no_header').innerText = data.no_kontrak;
                document.getElementById('detail_vendor').innerText = data.nama_vendor;
                document.getElementById('detail_tahun').innerText = data.tahun_kontrak;
                document.getElementById('detail_pengada').innerText = data.pihak_pengada;
                document.getElementById('detail_ket').innerText = data.keterangan || '-';

                // Populate Table Items
                const tbody = document.getElementById('detail_list_barang');
                tbody.innerHTML = '';

                if(data.barang && data.barang.length > 0) {
                    data.barang.forEach(item => {
                        let badgeClass = item.kondisi === 'Baik' ? 'bg-success' : 'bg-warning';
                        tbody.innerHTML += `
                            <tr>
                                <td class="fw-bold font-monospace">${item.kode_barcode}</td>
                                <td>${item.nama_barang}</td>
                                <td><span class="badge ${badgeClass}">${item.kondisi}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted fst-italic">Belum ada barang terdaftar di kontrak ini.</td></tr>';
                }
                
                // Store ID for switch button
                document.getElementById('edit_id_kontrak').value = data.id_kontrak;
            })
            .catch(err => {
                console.error(err);
                alert('Gagal mengambil data kontrak.');
            });
    }

    // --- 2. FETCH EDIT ---
    function fetchEdit(id) {
        currentKontrakId = id;

        // Close Detail modal if open
        const detailEl = document.getElementById('modalDetailKontrak');
        const detailModal = bootstrap.Modal.getInstance(detailEl);
        if(detailModal) detailModal.hide();

        fetch(`/kontrak/${id}`)
            .then(res => res.json())
            .then(data => {
                // Fill Form
                document.getElementById('edit_id_kontrak').value = data.id_kontrak;
                document.getElementById('edit_no_kontrak').value = data.no_kontrak;
                document.getElementById('edit_vendor').value = data.nama_vendor;
                document.getElementById('edit_tahun').value = data.tahun_kontrak;
                document.getElementById('edit_pengada').value = data.pihak_pengada;
                document.getElementById('edit_ket').value = data.keterangan || '';

                // Update Form Action URL
                let baseUrl = "{{ route('kontrak.update', ':id') }}";
                let updateUrl = baseUrl.replace(':id', data.id_kontrak);
                document.getElementById('formEditKontrak').action = updateUrl;

                // Show Modal
                new bootstrap.Modal(document.getElementById('modalEditKontrak')).show();
            })
            .catch(err => {
                console.error(err);
                alert('Gagal mengambil data untuk edit.');
            });
    }

    // --- 3. SWITCH MODAL ---
    function switchModalToEdit() {
        if(currentKontrakId) fetchEdit(currentKontrakId);
    }

    // --- 4. CONFIRM DELETE ---
    function confirmDelete(form) {
        return confirm('Apakah Anda yakin ingin menghapus Kontrak ini? \n\nPERHATIAN: Pastikan tidak ada barang yang terhubung dengan kontrak ini sebelum menghapus.');
    }
</script>
@endsection