@extends('layouts.app')
@section('title', 'Log Aktivitas Sistem')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Log Aktivitas Sistem</h3>
            <p class="text-muted">Pantau rekam jejak perubahan data yang dilakukan oleh pengguna.</p>
        </div>
        <div>
            <a href="{{ route('log_aktivitas.index') }}" class="btn btn-light border">
                <i class="bi bi-arrow-clockwise"></i> Segarkan Data
            </a>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('log_aktivitas.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Cari Pengguna</label>
                    <input type="text" name="search" class="form-control" placeholder="Username atau Nama..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Modul</label>
                    <select name="modul" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Modul</option>
                        @foreach($modulList as $m)
                            <option value="{{ $m }}" {{ request('modul') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Aksi</label>
                    <select name="aksi" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Aksi</option>
                        <option value="CREATE" {{ request('aksi') == 'CREATE' ? 'selected' : '' }}>CREATE</option>
                        <option value="UPDATE" {{ request('aksi') == 'UPDATE' ? 'selected' : '' }}>UPDATE</option>
                        <option value="DELETE" {{ request('aksi') == 'DELETE' ? 'selected' : '' }}>DELETE</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Tanggal</label>
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
                            <th class="ps-3">Waktu</th>
                            <th>Pengguna</th>
                            <th>Modul</th>
                            <th>Aksi</th>
                            <th>Keterangan Detail</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold">{{ $log->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }} WIB</small>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="fw-bold">{{ $log->user->karyawan->nama_karyawan ?? 'Administrator' }}</div>
                                        <small class="text-muted">{{ $log->user->username }} ({{ ucfirst($log->user->role) }})</small>
                                    @else
                                        <span class="text-muted fst-italic">Sistem / Terhapus</span>
                                    @endif
                                </td>
                                <td><span class="fw-semibold text-dark">{{ $log->modul }}</span></td>
                                <td>
                                    @php
                                        $badge = 'bg-secondary';
                                        if($log->aksi == 'CREATE') $badge = 'bg-success';
                                        elseif($log->aksi == 'UPDATE') $badge = 'bg-warning text-dark';
                                        elseif($log->aksi == 'DELETE') $badge = 'bg-danger';
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $log->aksi }}</span>
                                </td>
                                <td>
                                    <small class="d-block" style="max-width: 300px; white-space: normal;">
                                        {{ $log->keterangan }}
                                    </small>
                                </td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $log->ip_address ?? '-' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                    Belum ada rekaman aktivitas.
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