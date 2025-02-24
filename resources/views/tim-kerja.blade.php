<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />

  <title>Montify</title>
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
  <!-- ======= Header ======= -->
  @include('layouts.header')

  <!-- ======= Sidebar ======= -->
  @include('layouts.sidebar')

  <main id="main" class="main">
    <div class="pagetitle">
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item" style="font-size: 18px;">Master Data</li>
          <li class="breadcrumb-item active" style="font-size: 18px;"><a href="{{ route('tim-kerja') }}">Tim Kerja</a></li>
        </ol>
      </nav>
    </div>
    <!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Data Tim Kerja</h5>

              <!-- Search and Add Button -->
              <div class="d-flex justify-content-between mb-3">
                <!-- Search Bar -->
                <input type="text" class="form-control w-25" id="searchInput" placeholder="Cari Tim Kerja" value="{{ request('search') }}">

                <!-- Add Button -->
                <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addModal">
                  <i class="bi bi-plus me-1 text-white"></i> Tambah
                </button>
              </div>

              <!-- Table with hoverable rows -->
              <table class="table table-hover table-bordered text-center mb-4" id="dataTable">
                <thead>
                  <tr>
                    <th scope="col">Nomor</th>
                    <th scope="col">Kode Tim</th>
                    <th scope="col">Tim Kerja</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @include('timkerja.table')
                </tbody>
              </table>
              <!-- End Table with hoverable rows -->

              <!-- Records per page and Pagination -->
              <div class="d-flex justify-content-between align-items-center">
                <!-- Showing Text -->
                <div class="me-3">
                  <span>Showing {{ $timkerja->firstItem() }}-{{ $timkerja->lastItem() }} of {{ $timkerja->total() }} records</span>
                </div>

                <!-- Records Per Page Dropdown and Pagination at the right -->
                <div class="d-flex align-items-center ms-auto">
                  <!-- Records per page -->
                  <div class="me-3 d-flex align-items-center">
                    <span class="me-2">Records per page</span>
                    <select class="form-select w-auto" aria-label="Records per page" id="recordsPerPage" onchange="updatePageSize()">
                      <option value="10" {{ request('recordsPerPage') == 10 ? 'selected' : '' }}>10</option>
                      <option value="15" {{ request('recordsPerPage') == 15 ? 'selected' : '' }}>15</option>
                      <option value="20" {{ request('recordsPerPage') == 20 ? 'selected' : '' }}>20</option>
                    </select>
                  </div>

                  <!-- Pagination -->
                  <nav>
                    <ul class="pagination m-0">
                      @if ($timkerja->onFirstPage())
                      <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                      </li>
                      @else
                      <li class="page-item">
                        <a class="page-link" href="{{ $timkerja->previousPageUrl() }}">Previous</a>
                      </li>
                      @endif

                      @foreach ($timkerja->getUrlRange(1, $timkerja->lastPage()) as $page => $url)
                      <li class="page-item {{ $timkerja->currentPage() == $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                      </li>
                      @endforeach

                      @if ($timkerja->hasMorePages())
                      <li class="page-item">
                        <a class="page-link" href="{{ $timkerja->nextPageUrl() }}">Next</a>
                      </li>
                      @else
                      <li class="page-item disabled">
                        <span class="page-link">Next</span>
                      </li>
                      @endif
                    </ul>
                  </nav>
                </div>
              </div>
              <!-- End Pagination -->

              <!-- Modal Tambah Data Tim Kerja -->
              <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addModalLabel">Tambah Data Tim Kerja</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- Form tambah data tim kerja -->
                      <form action="{{ route('timkerja.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                          <label for="kode_tim" class="form-label">Kode Tim Kerja</label>
                          <input type="text" class="form-control" id="kode_tim" name="kode_tim" required>
                        </div>
                        <div class="mb-3">
                          <label for="nama_tim" class="form-label">Nama Tim Kerja</label>
                          <input type="text" class="form-control" id="nama_tim" name="nama_tim" required>
                        </div>
                        <!-- Tombol Simpan dan Cancel -->
                        <div class="d-flex justify-content-end mt-3">
                          <!-- Button Batal -->
                          <button type="button" class="btn text-white me-2" style="background-color: rgb(250, 82, 82);" data-bs-dismiss="modal" aria-label="Close">
                            Batal
                          </button>

                          <!-- Button Simpan -->
                          <button type="submit" class="btn btn-primary text-white">
                            Simpan
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Modal Tambah Data Tim Kerja -->

            </div>
          </div>
        </div>
      </div>
    </section>

  </main>
  <!-- End #main -->

  <!-- ======= Footer ======= -->
  @include('layouts.footer')

  <a
    href="#"
    class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="{{ asset('assets/js/tim-kerja.js') }}"></script>

  <!-- Tambahkan jQuery sebelum penutupan tag </body> -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Definisikan URL di dalam elemen HTML -->
  <span id="ajax-url" data-url="{{ route('timkerja.index') }}" style="display: none;"></span>

  <script>
    $(document).ready(function() {
      // Ambil URL dari elemen HTML
      var url = $('#ajax-url').data('url');

      // Tangani peristiwa 'keyup' pada kolom pencarian
      $('#searchInput').on('keyup', function() {
        var search = $(this).val(); // Ambil nilai pencarian
        var recordsPerPage = $('#recordsPerPage').val(); // Ambil jumlah data per halaman

        // Kirim permintaan Ajax ke server
        $.ajax({
          url: url,
          method: 'GET',
          data: {
            search: search,
            recordsPerPage: recordsPerPage
          },
          success: function(response) {
            // Perbarui tabel dengan data yang diterima
            $('#dataTable tbody').html(response);
          }
        });
      });
    });
  </script>

  <script>
    document.getElementById('recordsPerPage').addEventListener('change', function() {
      var recordsPerPage = this.value;
      var url = new URL(window.location.href);
      url.searchParams.set('recordsPerPage', recordsPerPage);
      window.location.href = url.toString();
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  @if (session('success'))
  <script>
    Swal.fire({
      icon: 'success',
      text: "{{ session('success') }}",
      showConfirmButton: true,
      timer: 3000
    });
  </script>
  @endif

  @if ($errors->has('kode_tim'))
  <script>
    Swal.fire({
      icon: 'error',
      text: 'Kode Tim sudah digunakan, masukkan kode lain',
      showConfirmButton: true,
      timer: 3000
    });
  </script>
  @endif

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
</body>

</html>