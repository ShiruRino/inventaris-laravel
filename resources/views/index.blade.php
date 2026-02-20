@extends('layouts.app')
@section('title','Dasbor Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Dasbor</h3>
        <p class="text-muted mb-0">Halo, Admin! Berikut ringkasan aset hari ini.</p>
    </div>
</div>

<div class="row mt-4 g-3">
    <div class="col-md-3">
        <div class="card bg-primary text-light p-3 h-100 shadow-sm border-0">
            <h6>Total Aset Unit</h6>
            <h1 class="text-center mt-2">{{$totalAset}}</h1>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-light p-3 h-100 shadow-sm border-0">
            <h6>Aset Personal</h6>
            <h1 class="text-center mt-2">{{$asetPersonal}}</h1>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark p-3 h-100 shadow-sm border-0">
            <h6>Di Gudang/Lokasi</h6>
            <h1 class="text-center mt-2">{{$asetNonPersonal}}</h1>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-light p-3 h-100 shadow-sm border-0">
            <h6>Rusak/Hilang</h6>
            <h1 class="text-center mt-2">{{$asetRuslang}}</h1>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-5 mb-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white fw-bold py-3">
                Komposisi Kondisi Aset
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <div style="width: 300px; height: 300px;">
                    <canvas id="chartKondisi"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7 mb-3">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white fw-bold py-3">
                Statistik Kategori Aset
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="chartKategori"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Chart 1: Kondisi (Donut Chart)
    const ctxKondisi = document.getElementById('chartKondisi').getContext('2d');
    new Chart(ctxKondisi, {
        type: 'doughnut',
        data: {
            labels: ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'],
            datasets: [{
                data: [{{$asetBaik}}, {{$asetRingan}}, {{ $asetBerat }}, {{$asetHilang}}],
                backgroundColor: ['#198754', '#ffc107', '#dc3545', '#212529'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Chart 2: Kategori (Bar Chart Dinamis)
    const ctxKategori = document.getElementById('chartKategori').getContext('2d');
    new Chart(ctxKategori, {
        type: 'bar',
        data: {
            // Mengambil label dari database yang dilempar via JSON
            labels: {!! json_encode($labelKategori ?? ['Data Kosong']) !!}, 
            datasets: [{
                label: 'Jumlah Unit',
                // Mengambil nilai angka dari database
                data: {!! json_encode($dataKategori ?? [0]) !!}, 
                backgroundColor: '#0d6efd',
                borderRadius: 4,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false } // Sembunyikan legend karena cuma 1 dataset
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: { precision: 0 } // Menghindari angka desimal (cth: 1.5 barang)
                }
            }
        }
    });
</script>
@endsection