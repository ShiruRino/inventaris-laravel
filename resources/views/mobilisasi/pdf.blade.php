<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Mobilisasi Aset</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .header p { margin: 5px 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Riwayat Mobilisasi Aset</h2>
        <p>Dicetak pada: {{ date('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu & Tanggal</th>
                <th>Kode Barcode</th>
                <th>Nama Barang</th>
                <th>Jenis Perpindahan</th>
                <th>Dari (Asal)</th>
                <th>Ke (Tujuan)</th>
                <th>Operator</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mobilisasi as $index => $m)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->barang->kode_barcode ?? '-' }}</td>
                    <td>{{ $m->barang->nama_barang ?? '-' }}</td>
                    <td>
                        @if($m->asal == "(Vendor)") Registrasi Awal
                        @elseif($m->id_penerima) Handover (Karyawan)
                        @else Relokasi (Ruangan)
                        @endif
                    </td>
                    <td>{{ $m->asal }}</td>
                    <td>
                        @if($m->id_penerima)
                            {{ $m->penerima->nama_karyawan }}
                        @else
                            {{ $m->lokasi_tujuan }}
                        @endif
                    </td>
                    <td>{{ $m->operator->karyawan->nama_karyawan ?? 'System' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data riwayat mobilisasi pada rentang waktu ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>