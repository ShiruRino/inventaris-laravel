use Illuminate\Support\Facades\Auth;
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { padding: 0; margin: 0; box-sizing: border-box; }
        .layout { display: grid; grid-template-columns: 280px 1fr; min-height: 100vh; } /* Fixed width sidebar is usually safer than % */
        .sidebar { background-color: #30364F; padding: 30px; color: #fff; height: 100vh; position: sticky; top: 0; }
        .nav-link-custom { color: #ccc; padding: 12px; text-decoration: none; display: block; border-radius: 5px; margin-bottom: 5px; transition: 0.3s; }
        .nav-link-custom:hover { background-color: #404765; color: #fff; }
        .nav-link-custom.active { background-color: #0d6efd; color: #fff; font-weight: bold; } /* Bootstrap primary blue */
        
        .user-panel { border-top: 1px solid #4a5270; margin-top: 20px; padding-top: 20px; }
        .content-area { background-color: #f8f9fa; padding: 2rem; }
    </style>
</head>
<body>
    <main class="layout">
        <aside class="sidebar d-flex flex-column justify-content-between">
            <div>
                <h3 class="text-center mb-4">Admin Panel</h3>
                <div class="d-flex flex-column">
                    <a href="{{ route('index') }}" 
                       class="nav-link-custom {{ request()->routeIs('index') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dasbor
                    </a>

                    <a href="" 
                       class="nav-link-custom {{ request()->routeIs('barang.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam me-2"></i> Barang
                    </a>

                    <a href="" 
                       class="nav-link-custom {{ request()->routeIs('kontrak.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text me-2"></i> Kontrak
                    </a>

                    <a href="#" class="nav-link-custom"> <i class="bi bi-people me-2"></i> User
                    </a>

                    <a href="{{route('karyawan.index')}}" class="nav-link-custom {{ request()->routeIs('karyawan.*') ? 'active' : '' }}"> <i class="bi bi-person-badge me-2"></i> Karyawan
                    </a>
                    
                    <a href="#" class="nav-link-custom"> <i class="bi bi-arrow-left-right me-2"></i> Mobilisasi
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
                        <span class="fw-bold">{{ Auth::user()->username ?? 'Guest' }}</span>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100 btn-sm">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')    
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>