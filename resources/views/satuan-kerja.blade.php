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
          <li class="breadcrumb-item active" style="font-size: 18px;"><a href="{{ route('satuan-kerja') }}">Satuan Kerja</a></li>
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
                <h5 class="card-title mb-0">Data Satuan Kerja</h5>
                <div>
                  <button class="btn btn-primary d-flex align-items-center mt-3" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus me-1 text-white"></i> Tambah
                  </button>
                </div>
              </div>

              <!-- Alert messages -->
              <div id="alert-container">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="font-size: 0.9rem;">
                  {{ session('success') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  {{ session('error') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('duplicate_errors'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert" style="white-space: pre-line; line-height: 1; font-size: 0.9rem;">
                  <strong class="mb-0 d-block" style="margin-bottom: 0;">Satuan kerja sudah pernah ditambahkan</strong>
                  <span style="display: block; margin-top: 0.1rem;">{!! nl2br(e(session('duplicate_errors'))) !!}</span>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if($errors->has('nama_satuan_kerja'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  Satuan kerja sudah ditambahkan
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
              </div>

              <!-- Search and Add Button -->
              <div class="d-flex justify-content-between mb-3">
                <!-- Search Bar -->
                <input type="text" class="form-control w-25" id="searchInput" placeholder="Cari Satuan Kerja">
              </div>

              <!-- Table with hoverable rows -->
              <table class="table table-hover table-bordered text-center mb-4" id="dataTable">
                <thead>
                  <tr>
                    <th scope="col">Nomor</th>
                    <th scope="col">Satuan Kerja</th>
                    <th scope="col">Kode Satuan Kerja</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @include('satuankerja.table')
                </tbody>
              </table>

              <!-- End Table with hoverable rows -->

              <!-- Records per page and Pagination -->
              <div class="d-flex justify-content-between align-items-center">
                <!-- Showing Text -->
                <div class="me-3">
                  <span id="showingText">Showing {{ $satuankerja->firstItem() }}-{{ $satuankerja->lastItem() }} of {{ $totalRecords }} records</span>
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
                      @if ($satuankerja->onFirstPage())
                      <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                      </li>
                      @else
                      <li class="page-item">
                        <a class="page-link" href="{{ $satuankerja->previousPageUrl() }}">Previous</a>
                      </li>
                      @endif

                      @foreach ($satuankerja->getUrlRange(1, $satuankerja->lastPage()) as $page => $url)
                      <li class="page-item {{ $satuankerja->currentPage() == $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                      </li>
                      @endforeach

                      @if ($satuankerja->hasMorePages())
                      <li class="page-item">
                        <a class="page-link" href="{{ $satuankerja->nextPageUrl() }}">Next</a>
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

              <!-- Modal Tambah Data Satuan Kerja -->
              <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content rounded-1">
                    <div class="modal-header">
                      <h5 class=" modal-title" id="addModalLabel">Tambah Data Satuan Kerja</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- Tab navigation -->
                      <ul class="nav nav-pills mb-4" id="addModalTab" role="tablist">
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
                          <form action="{{ route('satuankerja.store') }}" method="POST">
                            @csrf
                            <div class="mb-4 mt-3">
                              <label for="kode_satuan_kerja" class="form-label mb-0">Kode Satuan Kerja</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Masukkan kode satuan kerja yang terdiri dari 4 angka</small>
                              <input type="text" class="form-control mt-1" id="kode_satuan_kerja" name="kode_satuan_kerja" required>
                            </div>
                            <div class="mb-3">
                              <label for="nama_satuan_kerja" class="form-label mb-0">Nama Satuan Kerja</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Masukkan nama satuan kerja dengan huruf kapital tiap awal kata</small>
                              <input type="text" class="form-control mt-1" id="nama_satuan_kerja" name="nama_satuan_kerja" required>
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
                          <form action="{{ route('satuankerja.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <!-- Download Format Excel Link -->
                            <div class="mt-3">
                              <label class="form-label">Download Format Excel</label><br>
                              <a href="{{ route('satuankerja.download-format') }}" class="btn btn-link">Download Format Excel</a>
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
              <!-- End Modal Tambah Data Satuan Kerja -->

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

  <script src="{{ asset('assets/js/satuan-kerja.js') }}"></script>

  <!-- Tambahkan jQuery sebelum penutupan tag </body> -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Definisikan URL di dalam elemen HTML -->
  <span id="ajax-url" data-url="{{ route('satuankerja.index') }}" style="display: none;"></span>

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
            $('#dataTable tbody').html(response.table);

            // Perbarui teks 'Showing ... of ...' dengan jumlah hasil pencarian
            $('#showingText').text(response.showingText);
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