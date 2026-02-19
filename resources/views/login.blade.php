<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        *{
            padding: 0px;
            margin: 0px;
            box-sizing: border-box;
        }
        .login-box{
            width: 360px;
            height: 290px;
            background-color: #30364F;
            color: #E1D9BC; 
        }
    </style>
</head>
<body>
    <main class="d-flex flex-column gap-3 align-items-center justify-content-center" style="height: 100vh;">
        <h1>Login</h1>
        <div class="login-box">
            <form action="{{ route('login') }}" style="padding: 30px;" method="POST">
                @csrf    
                <div class="form-group mb-4">
                    <label for="" class="mb-2">Username</label>
                    <input type="text" name="username" placeholder="Masukkan username" class="form-control" style="background-color: #D9D9D9;" required>
                </div>
                <div class="form-group">
                    <label for="" class="mb-2">Password</label>
                    <input type="password" name="password" placeholder="Masukkan password" class="form-control" style="background-color: #D9D9D9;" required>
                </div>
                <div class="d-flex w-100 justify-content-center align-items-center" style="height: 80px;">
                    <button type="submit" class="btn btn-success" style="padding-left: 30px; padding-right: 30px;">Login</button>
                </div>
            </form>
        </div>
    </main>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
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
</html>