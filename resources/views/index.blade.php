<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />

  <title>Beranda - Montify</title>
  <meta content="" name="description" />
  <meta content="" name="keywords" />

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon" />
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon" />

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect" />
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet" />

  <!-- Vendor CSS Files -->
  <link
    href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}"
    rel="stylesheet" />
  <link
    href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}"
    rel="stylesheet" />
  <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet" />
</head>

<body>
  <!-- Header -->
  @include('layouts.header')

  <!-- Main Layout -->
  <div class="d-flex">

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Content -->
    <main id="main" class="main">
      <div class="pagetitle">
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a href="{{ route('index') }}" style="font-size: 18px;">Beranda</a>
            </li>
          </ol>
        </nav>
      </div>

      <!-- End Page Title -->

      <section class="section">
        <div class="row align-items-top">
          <!-- Full-width card at the top -->
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title mb-0">
                  <i class="bi bi-info-circle me-1"></i> Tentang Montify
                </h5>
                <!-- Teks yang sejajar dengan padding kiri, jarak lebih dekat -->
                <p class="custom-text ps-4 fs-8 small mb-0">
                  <b>MONTIFY</b> merupakan sistem informasi berbasis website yang digunakan untuk melakukan monitoring dan evaluasi kegiatan statistik di lingkungan BPS Provinsi Lampung.
                </p>
              </div>
            </div>

          </div>
          <!-- End Full-width Card -->

          <!-- Bottom Cards in a Single Row with 3 cards -->
          <div class="col-lg-3 col-md-3 mb-1">
            <a href="{{ route('dashboard') }}"> <!-- Wrap card in an anchor tag -->
              <div class="card">
                <div class="card-header text-center py-4" style="background-color: #228be6;">
                  <i class="bi bi-bar-chart-line fa-3x text-white"></i> <!-- Simbol di tengah -->
                </div>
                <div class="card-body">
                  <h5 class="card-title">Dashboard</h5>
                  <p class="card-text fs-8 small">Lihat Dashboard Kegiatan Statistik</p>
                </div>
              </div>
            </a>
          </div>

          <div class="col-lg-3 col-md-3 mb-1">
            <a href="{{ route('monitoring-kegiatan') }}"> <!-- Wrap card in an anchor tag -->
              <div class="card">
                <div class="card-header text-center py-4" style="background-color: #228be6;">
                  <i class="bi bi-graph-up fa-3x text-white"></i> <!-- Simbol di tengah -->
                </div>
                <div class="card-body">
                  <h5 class="card-title">Monitoring</h5>
                  <p class="card-text fs-8 small">Pencatatan Target dan Realisasi</p>
                </div>
              </div>
            </a>
          </div>

          <div class="col-lg-3 col-md-3 mb-1">
            <a href="{{ route('penilaian') }}"> <!-- Wrap card in an anchor tag -->
              <div class="card">
                <div class="card-header text-center py-4" style="background-color: #228be6;">
                  <i class="bi bi-clipboard-check fa-3x text-white"></i> <!-- Simbol di tengah -->
                </div>
                <div class="card-body">
                  <h5 class="card-title">Penilaian</h5>
                  <p class="card-text fs-8 small">Penilaian Kinerja Bulanan BPS Kabupaten/Kota</p>
                </div>
              </div>
            </a>
          </div>

          <div class="col-lg-3 col-md-3 mb-1">
            <a href="{{ route('sertifikat') }}"> <!-- Wrap card in an anchor tag -->
              <div class="card">
                <div class="card-header text-center py-4" style="background-color: #228be6;">
                  <i class="bi bi-archive fa-3x text-white"></i> <!-- Simbol di tengah -->
                </div>
                <div class="card-body">
                  <h5 class="card-title">Sertifikat</h5>
                  <p class="card-text fs-8 small">Lihat Sertifikat Kinerja Terbaik</p>
                </div>
              </div>
            </a>
          </div>
          <!-- End Bottom Cards -->
        </div>
      </section>

    </main>
  </div>

  <!-- ======= Footer ======= -->
  @include('layouts.footer')

  <a
    href="#"
    class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
  <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/quill/quill.js') }}"></script>
  <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
  <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <!-- Bootstrap Bundle JS -->
  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

</body>

</html>