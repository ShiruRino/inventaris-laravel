<?php

namespace App\Exports;

use App\Models\Mobilisasi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MobilisasiExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $search;
    protected $start_date;
    protected $end_date;

    public function __construct($search, $start_date, $end_date)
    {
        $this->search = $search;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function query()
    {
        $query = Mobilisasi::with(['barang', 'penerima', 'operator.karyawan']);

        if ($this->search != '') {
            $query->whereHas('barang', function($q) {
                $q->where('nama_barang', 'like', '%' . $this->search . '%')
                  ->orWhere('kode_barcode', 'like', '%' . $this->search . '%');
            })->orWhereHas('penerima', function($q) {
                $q->where('nama_karyawan', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->start_date != '') {
            $query->whereDate('created_at', '>=', $this->start_date);
        }

        if ($this->end_date != '') {
            $query->whereDate('created_at', '<=', $this->end_date);
        }

        return $query->latest('id_mobilisasi');
    }

    public function headings(): array
    {
        return [
            'Waktu & Tanggal',
            'Kode Barcode',
            'Nama Aset',
            'Jenis Transaksi',
            'Asal (Dari)',
            'Tujuan (Ke)',
            'Operator Sistem'
        ];
    }

    public function map($mobilisasi): array
    {
        $jenisTransaksi = 'Relokasi';
        if ($mobilisasi->asal == '(Vendor)') {
            $jenisTransaksi = 'Registrasi Awal';
        } elseif ($mobilisasi->id_penerima) {
            $jenisTransaksi = 'Handover';
        }

        $tujuan = $mobilisasi->id_penerima 
            ? ($mobilisasi->penerima->nama_karyawan ?? $mobilisasi->id_penerima) 
            : $mobilisasi->lokasi_tujuan;

        return [
            $mobilisasi->created_at->format('d M Y H:i'),
            $mobilisasi->barang->kode_barcode ?? '-',
            $mobilisasi->barang->nama_barang ?? '-',
            $jenisTransaksi,
            $mobilisasi->asal,
            $tujuan,
            $mobilisasi->operator->karyawan->nama_karyawan ?? 'System'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}