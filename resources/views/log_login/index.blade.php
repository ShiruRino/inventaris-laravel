@extends('layouts.app')
@section('title', 'Riwayat Sesi Login')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Pemantauan Sesi Pengguna</h3>
            <p class="text-muted">Pantau aktivitas login dan perangkat yang digunakan oleh pengguna sistem.</p>
        </div>
        <div>
            <a href="{{ route('log_login.index') }}" class="btn btn-light border">
                <i class="bi bi-arrow-clockwise"></i> Segarkan Data
            </a>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('log_login.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Cari Pengguna</label>
                    <input type="text" name="search" class="form-control" placeholder="Username atau Nama Karyawan..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Status Sesi</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif (Online)</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai (Offline)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Tanggal Login</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
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
                            <th class="ps-3">Pengguna</th>
                            <th>IP Address</th>
                            <th>Informasi Perangkat</th>
                            <th>Waktu Login</th>
                            <th>Waktu Logout</th>
                            <th>Status Sesi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">{{ $log->user->karyawan->nama_karyawan ?? 'Administrator' }}</div>
                                    <small class="text-muted">{{ $log->user->username }} ({{ ucfirst($log->user->role) }})</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">{{ $log->ip_address ?? 'Tidak diketahui' }}</span>
                                </td>
                                <td>
                                    <small class="d-inline-block text-truncate" style="max-width: 250px;" title="{{ $log->user_agent }}">
                                        {{ $log->user_agent ?? 'Tidak diketahui' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $log->waktu_login->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $log->waktu_login->format('H:i:s') }} WIB</small>
                                </td>
                                <td>
                                    @if($log->waktu_logout)
                                        <div class="fw-bold">{{ $log->waktu_logout->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $log->waktu_logout->format('H:i:s') }} WIB</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->status_sesi == 'Aktif')
                                        <span class="badge bg-success"><i class="bi bi-circle-fill small me-1"></i> Sedang Online</span>
                                    @else
                                        <span class="badge bg-secondary">Offline</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-hdd-network fs-1 d-block mb-2"></i>
                                    Belum ada data log sesi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-center">
                    {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection