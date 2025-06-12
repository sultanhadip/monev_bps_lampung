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
          <li class="breadcrumb-item active" style="font-size: 18px;"><a href="{{ route('data-kegiatan') }}">Kegiatan</a></li>
        </ol>
      </nav>
    </div>
    <!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <!-- Card Title and Add Button in the same row -->
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Data Kegiatan Statistik</h5>
                <div>
                  <button class="btn btn-primary d-flex align-items-center mt-3" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus me-1 text-white"></i> Tambah
                  </button>
                </div>
              </div>

              <div id="alert-container">
                {{-- Alert Success --}}
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="font-size: 0.9rem;">
                  {{ session('success') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                {{-- Alert General Error --}}
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size: 0.9rem;">
                  {{ session('error') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                {{-- Alert Duplikat --}}
                @if(session('duplicate_errors'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert" style="white-space: pre-line; line-height: 1.2; font-size: 0.9rem;">
                  <strong class="mb-1 d-block">Kegiatan sudah pernah ditambahkan:</strong>
                  <span>{!! nl2br(e(session('duplicate_errors'))) !!}</span>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                {{-- Alert Baris Tidak Valid --}}
                @if(session('error_rows'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="white-space: pre-line; line-height: 1.2; font-size: 0.9rem;">
                  <strong class="mb-1 d-block">Data tidak valid atau tidak lengkap:</strong>
                  <span>{!! nl2br(e(session('error_rows'))) !!}</span>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                {{-- Alert Validasi Nama Kegiatan --}}
                @if($errors->has('nama_kegiatan'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size: 0.9rem;">
                  Kegiatan sudah ditambahkan
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
              </div>

              <!-- Baris Search dan Filter -->
              <div class="d-flex mb-3" style="gap: 1rem;">
                <!-- Search Bar, 70% lebar -->
                <input
                  type="text"
                  class="form-control"
                  id="searchInput"
                  placeholder="Cari Kegiatan"
                  value="{{ request('search') }}"
                  style="flex: 7;" />

                <!-- Dropdown Filter Tim Kerja, 30% lebar -->
                <select
                  id="filterTim"
                  class="form-select"
                  style="flex: 3;">
                  <option value="">Pilih Tim Kerja</option>
                  @foreach ($timkerja as $tim)
                  <option value="{{ $tim->id }}" {{ request('filter_tim') == $tim->id ? 'selected' : '' }}>
                    {{ $tim->nama_tim }}
                  </option>
                  @endforeach
                </select>
              </div>

              <!-- Table with hoverable rows -->
              <table class="table table-hover table-bordered text-center mb-4" id="dataTable">
                <thead>
                  <tr>
                    <th scope="col">Nomor</th>
                    <th scope="col">Nama Kegiatan</th>
                    <th scope="col">Tim Kerja</th>
                    <th scope="col">Objek</th>
                    <th scope="col">Periode</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @include('datakegiatan.table')
                </tbody>
              </table>

              <!-- End Table with hoverable rows -->

              <!-- Records per page and Pagination -->
              <div class="d-flex justify-content-between align-items-center">
                <!-- Showing Text -->
                <div class="me-3">
                  <span id="showingText">Showing {{ $datakegiatan->firstItem() }}-{{ $datakegiatan->lastItem() }} of {{ $totalRecords }} records</span>
                </div>

                <!-- Records Per Page Dropdown and Pagination at the right -->
                <div class="d-flex align-items-center ms-auto">
                  <!-- Records per page -->
                  <div class="me-3 d-flex align-items-center">
                    <span class="me-2">Records per page</span>
                    <select class="form-select w-auto" aria-label="Records per page" id="recordsPerPage">
                      <option value="10" {{ request('recordsPerPage') == 10 ? 'selected' : '' }}>10</option>
                      <option value="15" {{ request('recordsPerPage') == 15 ? 'selected' : '' }}>15</option>
                      <option value="20" {{ request('recordsPerPage') == 20 ? 'selected' : '' }}>20</option>
                    </select>
                  </div>

                  <!-- Pagination -->
                  <nav>
                    <ul class="pagination m-0">
                      @if ($datakegiatan->onFirstPage())
                      <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                      </li>
                      @else
                      <li class="page-item">
                        <a class="page-link" href="{{ $datakegiatan->previousPageUrl() }}">Previous</a>
                      </li>
                      @endif

                      @foreach ($datakegiatan->getUrlRange(1, $datakegiatan->lastPage()) as $page => $url)
                      <li class="page-item {{ $datakegiatan->currentPage() == $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                      </li>
                      @endforeach

                      @if ($datakegiatan->hasMorePages())
                      <li class="page-item">
                        <a class="page-link" href="{{ $datakegiatan->nextPageUrl() }}">Next</a>
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

              <!-- Modal Tambah Data Kegiatan -->
              <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content rounded-1">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addModalLabel">Tambah Data Kegiatan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- Tab navigation -->
                      <ul class="nav nav-pills mb-3" id="addModalTab" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a class="nav-link active" id="manual-tab" data-bs-toggle="pill" href="#manual" role="tab" aria-controls="manual" aria-selected="true">
                            Tambah Manual
                          </a>
                        </li>
                        <li class="nav-item" role="presentation">
                          <a class="nav-link" id="excel-tab" data-bs-toggle="pill" href="#excel" role="tab" aria-controls="excel" aria-selected="false">
                            Import Excel
                          </a>
                        </li>
                      </ul>

                      <!-- Tab content -->
                      <div class="tab-content" id="addModalTabContent">
                        <!-- Tambah Manual Form -->
                        <div class="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                          <form action="{{ route('datakegiatan.store') }}" method="POST">
                            @csrf

                            <div class="mb-4 mt-3">
                              <label for="kode_tim" class="form-label mb-0">Tim Kerja</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Pilih Tim Kerja</small>
                              <select class="form-select mt-1" id="kode_tim" name="kode_tim" required>
                                <option value="">Pilih Tim Kerja</option>
                                @foreach ($timkerja as $tim)
                                <option value="{{ $tim->id }}">{{ $tim->nama_tim }}</option>
                                @endforeach
                              </select>
                            </div>

                            <div class="mb-4">
                              <label for="nama_kegiatan" class="form-label mb-0">Nama Kegiatan</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Masukkan nama kegiatan dengan huruf kapital tiap awal kata</small>
                              <input type="text" class="form-control mt-1" id="nama_kegiatan" name="nama_kegiatan" required>
                            </div>

                            <div class="mb-4">
                              <label for="objek_kegiatan" class="form-label mb-0">Objek Kegiatan</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Pilih objek kegiatan</small>
                              <select class="form-select mt-1" id="objek_kegiatan" name="objek_kegiatan" required>
                                <option value="">Pilih Objek Kegiatan</option>
                                <option value="Rumah Tangga">Rumah Tangga</option>
                                <option value="Usaha">Usaha</option>
                                <option value="Lainnya">Lainnya</option>
                              </select>
                            </div>

                            <div class="mb-4">
                              <label for="periode_kegiatan" class="form-label mb-0">Periode Kegiatan</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Pilih periode kegiatan</small>
                              <select class="form-select mt-1" id="periode_kegiatan" name="periode_kegiatan" required data-bs-menu-direction="down">
                                <option value="">Pilih Periode Kegiatan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Triwulan">Triwulan</option>
                                <option value="Semesteran">Semesteran</option>
                                <option value="Tahunan">Tahunan</option>
                              </select>
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

                        <!-- Import Excel Form -->
                        <div class="tab-pane fade" id="excel" role="tabpanel" aria-labelledby="excel-tab">
                          <form action="{{ route('datakegiatan.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <!-- Download Format Excel Link -->
                            <div class="mt-3">
                              <label class="form-label">Download Format Excel</label><br>
                              <a href="{{ route('datakegiatan.download-format') }}" class="btn btn-link">Download Format Excel</a>
                            </div>

                            <!-- Upload Excel File -->
                            <div class="mt-3">
                              <label for="excel_file" class="form-label">Pilih File Excel</label>
                              <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xls,.xlsx" required>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                              <button type="button" class="btn text-white me-2" style="background-color: rgb(250, 82, 82);" data-bs-dismiss="modal" aria-label="Close">
                                Batal
                              </button>
                              <button type="submit" class="btn btn-primary text-white">
                                Import
                              </button>
                            </div>
                          </form>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Modal Tambah Data Kegiatan -->

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

  <script src="{{ asset('assets/js/data-kegiatan.js') }}"></script>

  <!-- Tambahkan jQuery sebelum penutupan tag </body> -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Definisikan URL di dalam elemen HTML -->
  <span id="ajax-url" data-url="{{ route('datakegiatan.index') }}" style="display: none;"></span>

  <script>
    $(document).ready(function() {
      // Ambil URL dari elemen HTML
      var url = $('#ajax-url').data('url');

      // Fungsi untuk fetch data dengan search, recordsPerPage, dan filterTim
      function fetchData() {
        var search = $('#searchInput').val(); // Ambil nilai pencarian
        var recordsPerPage = $('#recordsPerPage').val(); // Ambil jumlah data per halaman
        var filterTim = $('#filterTim').val(); // Ambil nilai dropdown filter tim kerja

        // Kirim permintaan Ajax ke server dengan semua parameter
        $.ajax({
          url: url,
          method: 'GET',
          data: {
            search: search,
            recordsPerPage: recordsPerPage,
            filter_tim: filterTim
          },
          success: function(response) {
            // Perbarui tabel dengan data yang diterima
            $('#dataTable tbody').html(response.table);

            // Perbarui teks 'Showing ... of ...' dengan jumlah hasil pencarian
            $('#showingText').text(response.showingText);
          }
        });
      }

      // Event handler saat mengetik di search input
      $('#searchInput').on('keyup', fetchData);

      // Event handler saat mengubah filter dropdown
      $('#filterTim').on('change', fetchData);
    });
  </script>

  <script>
    document.getElementById('recordsPerPage').addEventListener('change', function() {
      var recordsPerPage = this.value;
      var url = new URL(window.location.href);

      // Update parameter recordsPerPage di URL
      url.searchParams.set('recordsPerPage', recordsPerPage);

      // Ambil nilai filterTim dari dropdown dan update URL juga
      var filterTim = document.getElementById('filterTim').value;
      if (filterTim) {
        url.searchParams.set('filter_tim', filterTim);
      } else {
        url.searchParams.delete('filter_tim');
      }

      // Redirect ke URL baru dengan parameter lengkap
      window.location.href = url.toString();
    });
  </script>

  <script>
    window.routes = {
      pendingVerifikasi: "{{ route('notifications.pending-verifikasi') }}"
    };
  </script>

  <script>
    setTimeout(() => {
      const alertNode = document.querySelector('#alert-container .alert');
      if (alertNode) {
        // Bootstrap 5 way to close alert programmatically
        let alert = new bootstrap.Alert(alertNode);
        alert.close();
      }
    }, 4000); // 4 detik
  </script>

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

  <script src="{{ asset('assets/js/notification.js') }}"></script>
</body>

</html>