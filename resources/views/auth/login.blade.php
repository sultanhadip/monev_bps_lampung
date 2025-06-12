<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Montify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* Perkecil ukuran font pesan kesalahan */
        .error-message {
            font-size: 0.875rem;
            /* 14px */
        }

        /* Menambahkan border merah pada input password saat error */
        .input-error {
            border-color: red !important;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row login-container shadow-lg bg-white rounded overflow-hidden w-75">
            <!-- Left Section -->
            <div class="col-md-5 d-flex align-items-center justify-content-center text-white text-center p-0">
                <img src="assets/img/login-image.jpg" class="img-fluid w-100 h-100 object-fit-cover" alt="Login Image">
            </div>

            <!-- Right Section -->
            <div class="col-md-7 p-5">
                <!-- Logo dan Teks MONTIFY di tengah dengan ukuran gambar diperkecil -->
                <div class="logo d-flex justify-content-center align-items-center mb-4">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" style="width: 40px; height: auto;" />
                </div>

                <!-- Teks LOGIN -->
                <h3 class="fw-bold text-center text-primary mt-0 mb-4">LOGIN MONTIFY</h3>
                <form action="{{ url('authenticate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <input type="text" name="username" id="username" class="form-control @if($errors->has('login_error')) input-error @endif" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <!-- <span class="input-group-text"><i class="fas fa-lock"></i></span> -->
                            <input type="password" name="password" id="password" class="form-control @if($errors->has('login_error')) input-error @endif" required>
                        </div>

                        <!-- Menampilkan Pesan Kesalahan di bawah input password -->
                        @if ($errors->has('login_error'))
                        <span class="text-danger error-message mt-2">{{ $errors->first('login_error') }}</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" value="true">
                            <label class="form-check-label" for="rememberMe">Ingat saya di perangkat ini</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 text-white">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>