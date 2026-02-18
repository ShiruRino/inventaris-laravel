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
                    <input type="text" name="username" placeholder="Input your username" class="form-control" style="background-color: #D9D9D9;">
                </div>
                <div class="form-group">
                    <label for="" class="mb-2">Password</label>
                    <input type="password" name="password" placeholder="Input your password" class="form-control" style="background-color: #D9D9D9;">
                </div>
                <div class="d-flex w-100 justify-content-center align-items-center" style="height: 80px;">
                    <button type="submit" class="btn btn-success" style="padding-left: 30px; padding-right: 30px;">Login</button>
                </div>
            </form>
        </div>
    </main>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>