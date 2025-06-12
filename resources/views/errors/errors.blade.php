<!DOCTYPE html>

<html lang="en" data-theme="emerald">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPS Provinsi Lampung</title>

    <meta name="author" content="">
    <meta name="description" content="">
    <link rel="icon" href="{{ asset('assets/logo/logo.ico') }}" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/AOS.css') }}">
    <link rel="stylesheet" href="{{ asset('css/all.css') }}">

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/all.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/alert.js') }}"></script>
    <script src="{{ asset('js/AOS.min.js') }}"></script>

    @stack('css')
</head>

<body>
    <div class="w-full">
        <div class="flex flex-col max-w-7xl h-screen mx-auto pt-10 items-center justify-center">
            <div>
                <i class="fa-duotone fa-cloud-exclamation text-6xl"></i>
            </div>
            <div class="max-w-2xl mx-auto p-4 sm:p-6 md:p-8 text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4 sm:mb-4">
                    {{ $statusCode }}: Error
                </h1>
                <p class="mb-3 sm:mb-5">
                    Terjadi kesalahan. Klik tombol di bawah untuk kembali ke halaman sebelumnya.
                </p>
                <button class="btn btn-neutral" onclick="window.history.back()">
                    Kembali
                </button>
            </div>
        </div>
    </div>
</body>

</html>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        const themeToggle = document.getElementById('theme-toggle');
        const currentTheme = localStorage.getItem('theme') || 'corporate';
        document.documentElement.setAttribute('data-theme', currentTheme);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.addEventListener('load', function() {
            AOS.init({
                duration: 1500,
                once: true,
            });
        });
    });
</script>