@extends('layouts.app')
@section('title','Dasbor Admin')
@section('content')
<h3 class="mb-3">Dasbor</h3>
<h5 class="text-muted">Halo, Admin! Berikut ringkasan aset hari ini.</h5>

<div class="row mt-4 g-3">
    <div class="col-md-3">
        <div class="card bg-primary text-light p-3 h-100">
            <h6>Total Aset</h6>
            <h1 class="text-center mt-2">{{$totalAset}}</h1>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-light p-3 h-100">
            <h6>Aset Personal</h6>
            <h1 class="text-center mt-2">{{$asetPersonal}}</h1>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark p-3 h-100">
            <h6>Di Gudang</h6>
            <h1 class="text-center mt-2">{{$asetNonPersonal}}</h1>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-light p-3 h-100">
            <h6>Rusak/Hilang</h6>
            <h1 class="text-center mt-2">{{$asetRuslang}}</h1>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-5 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white fw-bold py-3">
                Komposisi Kondisi Aset
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <div style="width: 300px;">
                    <canvas id="chartKondisi"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="col-md-7 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white fw-bold py-3">
                5 Kategori Aset Terbanyak
            </div>
            <div class="card-body">
                <canvas id="chartKategori"></canvas>
            </div>
        </div>
    </div> -->
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Fungsi Logout Sederhana
        function logout() {
            if(confirm("Apakah Anda yakin ingin keluar dari sistem?")) {
                // Di sini nanti arahkan ke file login.php atau login.html
                alert("Anda berhasil logout!");
                window.location.href = "login.html"; 
            }
        }

        // Chart 1: Kondisi (Donut Chart)
        const ctxKondisi = document.getElementById('chartKondisi').getContext('2d');
        new Chart(ctxKondisi, {
            type: 'doughnut',
            data: {
                labels: ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'],
                datasets: [{
                    data: [{{$asetBaik}}, {{$asetRingan}}, {{ $asetBerat }}, {{$asetHilang}}],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Chart 2: Kategori (Bar Chart)
        const ctxKategori = document.getElementById('chartKategori').getContext('2d');
        new Chart(ctxKategori, {
            type: 'bar',
            data: {
                labels: ['Laptop', 'Kursi', 'Meja', 'Monitor', 'Proyektor'],
                datasets: [{
                    label: 'Jumlah Unit',
                    data: [50, 120, 80, 45, 10],
                    backgroundColor: '#0d6efd',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
@endsection