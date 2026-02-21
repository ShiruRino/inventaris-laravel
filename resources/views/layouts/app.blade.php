<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    {{-- 1. SWEETALERT CDN --}}
    

    <style>
        * { padding: 0; margin: 0; box-sizing: border-box; }
        .layout { display: grid; grid-template-columns: 20% 80%; min-height: 100vh; } 
        .sidebar { background-color: #30364F; padding: 30px; color: #fff; height: 100vh; position: sticky; top: 0; }
        .nav-link-custom { color: #ccc; padding: 12px; text-decoration: none; display: block; border-radius: 5px; margin-bottom: 5px; transition: 0.3s; }
        .nav-link-custom:hover, .nav-link-custom.active { background-color: #404765; color: #fff; }
        
        .user-panel { border-top: 1px solid #4a5270; margin-top: 20px; padding-top: 20px; }
        .content-area { background-color: #f8f9fa; padding: 2rem; }
    </style>
</head>
<body>
    <main class="layout">
        <aside class="sidebar d-flex flex-column justify-content-between">
            {{-- SIDEBAR CONTENT (Keep your existing sidebar code here) --}}
            <div>
                <h3 class="text-center mb-4">Admin Panel</h3>
                <div class="d-flex flex-column">
                    <a href="{{ route('index') }}" class="nav-link-custom {{ request()->routeIs('index') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dasbor
                    </a>
                    <a href="{{ route('karyawan.index') }}" class="nav-link-custom {{ request()->routeIs('karyawan.*') ? 'active' : '' }}"> 
                        <i class="bi bi-person-badge me-2"></i> Karyawan
                    </a>
                    <a href="{{ route('kontrak.index') }}" class="nav-link-custom {{ request()->routeIs('kontrak.*') ? 'active' : '' }}"> 
                        <i class="bi bi-file-earmark-text me-2"></i> Kontrak
                    </a>
                    <a href="{{ route('barang.index') }}" class="nav-link-custom {{ request()->routeIs('barang.*') ? 'active' : '' }}"> 
                        <i class="bi bi-box-seam me-2"></i> Barang
                    </a>
                    <a href="{{ route('mobilisasi.index') }}" class="nav-link-custom {{ request()->routeIs('mobilisasi.*') ? 'active' : '' }}"> 
                        <i class="bi bi-arrow-left-right me-2"></i> Mobilisasi
                    </a>
                    <a href="{{ route('tugas.index') }}" class="nav-link-custom {{ request()->routeIs('tugas.*') ? 'active' : '' }}"> 
                        <i class="bi bi-clipboard me-2"></i> Tugas
                    </a>
                    <a href="{{ route('user.index') }}" class="nav-link-custom {{ request()->routeIs('user.*') ? 'active' : '' }}"> 
                        <i class="bi bi-people me-2"></i> User
                    </a>
                    <a href="{{ route('log_login.index') }}" class="nav-link-custom {{ request()->routeIs('log_login.*') ? 'active' : '' }}"> 
                        <i class="bi bi-clock-history me-2"></i> Log Login
                    </a>
                    <a href="{{ route('log_aktivitas.index') }}" class="nav-link-custom {{ request()->routeIs('log_aktivitas.*') ? 'active' : '' }}"> 
                        <i class="bi bi-journal-text me-2"></i> Log Aktivitas
                    </a>
                </div>
            </div>
            
            <div class="mt-auto user-panel">
                 <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <i class="bi bi-person-fill h5 m-0 text-white"></i>
                    </div>
                    <div>
                        <small class="d-block text-light" style="font-size: 0.75rem;">Login sebagai:</small>
                        <span class="fw-bold">{{Auth::user()->username}}</span>
                    </div>
                </div>
                 <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100 btn-sm">Logout</button>
                </form>
            </div>
        </aside>

        <div class="content-area">
            @yield('content')    
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {{-- ========================================== --}}
    {{--   SWEETALERT NOTIFICATION LOGIC            --}}
    {{-- ========================================== --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // 1. Configure the "Toast" style (Top Right, Timer, ProgressBar)
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // 2. Handle Success Session
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: '{{ session('success') }}'
            });
        @endif

        // 3. Handle Error Session (Manual redirect with 'error')
        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: '{{ session('error') }}'
            });
        @endif

        // 4. Handle Validator Errors ($errors)
        // These are generated when $request->validate() fails
        @if($errors->any())
            // Capture all errors into an HTML list
            let errorHtml = '<ul class="text-start fs-6">';
            @foreach ($errors->all() as $error)
                errorHtml += '<li>{{ $error }}</li>';
            @endforeach
            errorHtml += '</ul>';

            Toast.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                html: errorHtml,
                timer: 5000 // A bit longer so user can read the list
            });
        @endif
    </script>
</body>
</html>