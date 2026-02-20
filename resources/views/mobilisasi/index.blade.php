@extends('layouts.app')
@section('title', "Data Mobilisasi Aset")

@section('content')
    <style>
        .timeline-date { font-size: 0.85rem; color: #6c757d; }
        .avatar-tiny { width: 24px; height: 24px; object-fit: cover; border-radius: 50%; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Riwayat Mobilisasi Aset</h3>
            <p class="text-muted">Lacak pergerakan aset (Handover Personal & Relokasi Ruangan).</p>
        </div>
        <div>
            <a href="{{ route('mobilisasi.export', request()->query()) }}" class="btn btn-outline-success me-2">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalManualMobilisasi">
                <i class="bi bi-arrow-left-right"></i> Input Perpindahan
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
            <form action="{{ route('mobilisasi.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cari Aset / Nama Orang</label>
                    <input type="text" name="search" class="form-control" placeholder="Ketik nama barang atau karyawan..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> Filter</button>
                    <a href="{{ route('mobilisasi.index') }}" class="btn btn-light border"><i class="bi bi-arrow-clockwise"></i></a>
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
                            <th class="ps-3">Waktu & Tanggal</th>
                            <th>Nama Aset</th>
                            <th>Jenis Transaksi</th>
                            <th>Dari (Asal)</th>
                            <th>Ke (Tujuan)</th>
                            <th>Operator</th>
                            <th>Lampiran Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mobilisasi as $m)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">{{ $m->created_at->format('d M Y') }}</div>
                                    <div class="timeline-date">{{ $m->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td>
                                    <span class="d-block fw-bold">{{ $m->barang->nama_barang ?? '-' }}</span>
                                    <small class="text-muted">{{ $m->barang->kode_barcode ?? 'No Code' }}</small>
                                </td>
                                <td>
                                    @if($m->asal == "(Vendor)")
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                        <i class="bi bi-building me-1"></i> Registrasi Awal
                                    </span>
                                    @elseif ($m->id_penerima)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                        <i class="bi bi-person me-1"></i> Handover
                                    </span>
                                    @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">
                                        <i class="bi bi-geo-alt me-1"></i> Relokasi
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fst-italic">{{$m->asal}}</div>
                                </td>
                                <td>
                                    @if($m->id_penerima)
                                        <i class="bi bi-person-check-fill text-success"></i> <strong>{{ $m->penerima->nama_karyawan }}</strong>
                                    @else
                                        <i class="bi bi-geo-alt-fill text-danger"></i> <strong>{{ $m->lokasi_tujuan }}</strong>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $m->operator->karyawan->nama_karyawan ?? 'System' }}</small>
                                </td>
                                <td>
                                    @if($m->bukti_serah_terima)
                                        <a href="{{ asset('storage/'.$m->bukti_serah_terima) }}" target="_blank" class="btn btn-sm btn-light border" title="Lihat Bukti">
                                            <i class="bi bi-image text-primary"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data mobilisasi aset.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-center">
                {{ $mobilisasi->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalManualMobilisasi" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Manual Perpindahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <form action="{{ route('mobilisasi.store') }}" method="POST" id="formMobilisasi" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle me-1"></i> Pilih Kontrak dulu untuk memuat daftar barang.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Kontrak / Vendor</label>
                            <select class="form-select" id="selectKontrak" onchange="fetchBarangByKontrak(this.value)">
                                <option value="">-- Pilih Kontrak --</option>
                                @foreach($kontrak as $k)
                                    <option value="{{ $k->id_kontrak }}">
                                        {{ $k->no_kontrak }} - {{ $k->nama_vendor }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Barang <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_barang" id="selectBarang" required disabled>
                                <option value="">-- Menunggu Pilihan Kontrak --</option>
                            </select>
                            <div id="loadingBarang" class="form-text text-primary d-none">
                                <span class="spinner-border spinner-border-sm" role="status"></span> Memuat data barang...
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block fw-bold">Jenis Transaksi</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_transaksi" id="optKaryawan" value="karyawan" checked onchange="toggleTujuan()">
                                <label class="form-check-label" for="optKaryawan">Ke Karyawan (Handover)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_transaksi" id="optLokasi" value="lokasi" onchange="toggleTujuan()">
                                <label class="form-check-label" for="optLokasi">Ke Lokasi (Relokasi)</label>
                            </div>
                        </div>

                        <div class="mb-3" id="fieldKaryawan">
                            <label class="form-label">NIP Karyawan Penerima <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nip_penerima" id="inputNipPenerima" placeholder="Ketik NIP..." onkeyup="cariKaryawan(this.value)">
                            <div id="karyawanResult" class="form-text text-muted mt-1">Masukkan NIP untuk mencari.</div>
                        </div>

                        <div class="mb-3 d-none" id="fieldLokasi">
                            <label class="form-label">Lokasi Tujuan Fisik <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="lokasi_tujuan" placeholder="Contoh: R. Meeting Lt. 2">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bukti Serah Terima (Foto/Dokumen)</label>
                            <input type="file" class="form-control" name="bukti_serah_terima" accept="image/*,.pdf">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perpindahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let typingTimer;
        const doneTypingInterval = 500; 

        function cariKaryawan(nip) {
            clearTimeout(typingTimer);
            const resultDiv = document.getElementById('karyawanResult');

            if (nip.length < 3) {
                resultDiv.innerHTML = 'Masukkan NIP untuk mencari.';
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

        function fetchBarangByKontrak(kontrakId) {
            const selectBarang = document.getElementById('selectBarang');
            const loadingText = document.getElementById('loadingBarang');

            selectBarang.innerHTML = '<option value="">-- Pilih Barang --</option>';
            selectBarang.disabled = true;

            if (!kontrakId) {
                selectBarang.innerHTML = '<option value="">-- Menunggu Pilihan Kontrak --</option>';
                return;
            }

            loadingText.classList.remove('d-none');

            fetch(`/barang/kontrak/${kontrakId}`)
                .then(response => response.json())
                .then(data => {
                    loadingText.classList.add('d-none');
                    selectBarang.disabled = false;

                    if (data.length === 0) {
                        selectBarang.innerHTML = '<option value="">-- Tidak ada barang di kontrak ini --</option>';
                    } else {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id_barang;
                            option.textContent = `${item.kode_barcode} - ${item.nama_barang}`;
                            selectBarang.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    loadingText.classList.add('d-none');
                    alert('Gagal mengambil data barang.');
                });
        }

        function toggleTujuan() {
            const isKaryawan = document.getElementById('optKaryawan').checked;
            const fieldKaryawan = document.getElementById('fieldKaryawan');
            const inputKaryawan = document.getElementById('inputNipPenerima'); 
            
            const fieldLokasi = document.getElementById('fieldLokasi');
            const inputLokasi = fieldLokasi.querySelector('input');

            if (isKaryawan) {
                fieldKaryawan.classList.remove('d-none');
                inputKaryawan.required = true;
                
                fieldLokasi.classList.add('d-none');
                inputLokasi.required = false;
                inputLokasi.value = ''; 
            } else {
                fieldKaryawan.classList.add('d-none');
                inputKaryawan.required = false;
                inputKaryawan.value = ''; 
                
                document.getElementById('karyawanResult').innerHTML = 'Masukkan NIP untuk mencari.';
                document.getElementById('karyawanResult').className = 'form-text text-muted mt-1';
                
                fieldLokasi.classList.remove('d-none');
                inputLokasi.required = true;
            }
        }
    </script>
@endsection