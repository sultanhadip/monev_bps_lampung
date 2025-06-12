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
          <li class="breadcrumb-item" style="font-size: 18px;"><a href="{{ route('monitoring-kegiatan') }}">Monitoring</a></li>
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
                <h5 class="card-title mb-0">Monitoring Kegiatan</h5>
                <div>
                  @canAny(['isAdmin','isAdminProv'])
                  <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus me-1"></i> Tambah
                  </button>
                  @endcanAny
                </div>
              </div>

              <!-- Alert messages -->
              @if(session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert" style="font-size: 0.9rem;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              @endif

              @if(session('duplicate_errors'))
              <div class="alert alert-warning alert-dismissible fade show" role="alert" style="white-space: pre-line; line-height: 1; font-size: 0.9rem;">
                <strong class="mb-0 d-block" style="margin-bottom: 0;">Kegiatan sudah pernah ditambahkan</strong>
                <span style="display: block; margin-top: 0.1rem;">{!! nl2br(e(session('duplicate_errors'))) !!}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              @endif

              @if(session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              @endif

              <!-- Baris 2: Form Filter (mengisi full width) -->
              <div id="filterForm" class="row g-2 align-items-center mb-3">

                <!-- Search input: 6 kolom -->
                <div class="col-md-6">
                  <input
                    id="searchInput"
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="Masukkan kata kunci pencarian" />
                </div>

                <!-- Filter Bulan: 3 kolom -->
                <div class="col-md-3">
                  <select
                    id="filter_bulan"
                    name="filter_bulan"
                    class="form-select">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}"
                      {{ (int)request('filter_bulan') === $m ? 'selected' : '' }}>
                      {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                    @endforeach
                  </select>
                </div>

                <!-- Filter Tahun: 3 kolom -->
                <div class="col-md-3">
                  <select
                    id="filter_tahun"
                    name="filter_tahun"
                    class="form-select">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $yr)
                    <option value="{{ $yr }}"
                      {{ request('filter_tahun') == $yr ? 'selected' : '' }}>
                      {{ $yr }}
                    </option>
                    @endforeach
                  </select>
                </div>

              </div>

              <!-- Table with hoverable rows and horizontal scroll -->
              <div style="overflow-x: auto;">
                <table class="table table-hover table-bordered text-center mb-4" id="dataTable">
                  <thead>
                    <tr class="text-center align-middle">
                      <th scope="col">Nomor</th>
                      <th scope="col">Tim Kerja</th>
                      <th scope="col">Nama Kegiatan</th>
                      <th scope="col">Periode</th>
                      <th scope="col">Target</th>
                      <th scope="col">Realisasi</th>
                      <th scope="col">Persentase</th>
                      <th scope="col">Waktu Kegiatan</th>
                      <th scope="col">Status</th>
                      @if ($canAccessVerifikasi)
                      <th scope="col">Action</th>
                      @endif

                    </tr>
                  </thead>
                  <tbody>
                    @include('monitoringkegiatan.table', ['monitoringKegiatan' => $monitoringKegiatan, 'canAccessVerifikasi' => $canAccessVerifikasi])
                  </tbody>
                </table>
              </div>
              <!-- End Table with hoverable rows and horizontal scroll -->

              <!-- Records per page and Pagination -->
              <div class="d-flex justify-content-between align-items-center">
                <!-- Showing Text -->
                <div class="me-3">
                  <span id="showingText">
                    Showing {{ $monitoringKegiatan->firstItem() }}-{{ $monitoringKegiatan->lastItem() }} of {{ $monitoringKegiatan->total() }} records
                  </span>
                </div>

                <!-- Records Per Page Dropdown and Pagination at the right -->
                <div class="d-flex align-items-center ms-auto">
                  <!-- Records per page -->
                  <div class="me-3 d-flex align-items-center">
                    <span class="me-2">Records per page</span>
                    <select id="per_page" class="form-select w-auto">
                      <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                      <option value="15" {{ request('per_page', 10) == 15 ? 'selected' : '' }}>15</option>
                      <option value="20" {{ request('per_page', 10) == 20 ? 'selected' : '' }}>20</option>
                    </select>
                  </div>

                  <div id="paginationContainer">
                    @include('monitoringkegiatan.pagination', ['monitoringKegiatan' => $monitoringKegiatan])
                  </div>

                </div>
              </div>
              <!-- End Pagination -->

              <!-- Modal Tambah Data Monitoring Kegiatan -->
              <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content rounded-1">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addModalLabel">Tambah Monitoring Kegiatan</h5>
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
                          <form action="{{ route('monitoring-kegiatan.store') }}" method="POST">
                            @csrf
                            <!-- Form Fields -->
                            <div class="mb-4 mt-3">
                              <label for="kode_tim" class="form-label mb-0">Tim Kerja</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Pilih tim kerja</small>
                              <select class="form-select mt-1" id="kode_tim" name="kode_tim" required>
                                <option value="" disabled selected>Pilih Tim Kerja</option>
                                @foreach($timkerja as $tim)
                                <option value="{{ $tim->id }}">{{ $tim->nama_tim }}</option>
                                @endforeach
                              </select>
                            </div>

                            <!-- Data Kegiatan (dikirim lewat data attributes) -->
                            <div id="datakegiatan" data-kegiatan="{{ json_encode($datakegiatan) }}" style="display: none;"></div>

                            <!-- Form Fields for Kegiatan, Tahun, Bulan, etc -->
                            <div class="mb-4" id="kode_kegiatan-container">
                              <label for="kode_kegiatan" class="form-label mb-0">Nama Kegiatan</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Pilih nama kegiatan yang akan dimonitoring</small>
                              <select class="form-select mt-1" id="kode_kegiatan" name="kode_kegiatan" required>
                                <option value="" disabled selected>Pilih Kegiatan</option>
                              </select>
                            </div>

                            <!-- Input Waktu Mulai dan Waktu Selesai -->
                            <div class="mb-4">
                              <label for="waktu_mulai" class="form-label mb-0">Waktu Mulai</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Pilih tanggal mulai</small>
                              <input type="date" class="form-control mt-1" id="waktu_mulai" name="waktu_mulai" required>
                            </div>

                            <div class="mb-4">
                              <label for="waktu_selesai" class="form-label mb-0">Waktu Selesai</label>
                              <br>
                              <small class="form-text text-muted mt-0 mb-3">Pilih tanggal selesai</small>
                              <input type="date" class="form-control mt-1" id="waktu_selesai" name="waktu_selesai" required>
                            </div>

                            <!-- Alokasi Target Sampel -->
                            <div class="mb-0">
                              <label for="satuan_kerja" class="form-label mb-0">Alokasi Target</label>

                              <br>
                              <small class="form-text text-muted mb-3">*Pilih satuan kerja yang akan diberikan target sampel</small>

                              <div id="checkboxes" class="mt-1">
                                <div class="mb-0">
                                  <input type="checkbox" id="select_all" class="checkbox" style="margin-right: 5px;">
                                  <label for="select_all" class="mb-0">
                                    <p class="d-inline-block mb-0"><strong>Pilih Semua</strong></p>
                                  </label>
                                </div>

                                @foreach($satuankerja as $satker)
                                <div class="mb-0">
                                  <input type="checkbox" name="satuan_kerja[]" value="{{ $satker->id }}" id="satuan_kerja_{{ $satker->id }}" class="checkbox satuan-checkbox" style="margin-right: 5px;">
                                  <label for="satuan_kerja_{{ $satker->id }}" class="mb-0">
                                    <p class="d-inline-block mb-0">[{{ $satker->kode_satuan_kerja }}] {{ $satker->nama_satuan_kerja }}</p>
                                  </label>
                                </div>
                                @endforeach
                              </div>

                              <div id="target-sampel-container" class="mt-3"></div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                              <button type="button" class="btn text-white me-2" style="background-color: rgb(250, 82, 82);" data-bs-dismiss="modal" aria-label="Close">
                                Batal
                              </button>
                              <button type="submit" class="btn btn-primary text-white">
                                Simpan
                              </button>
                            </div>
                          </form>
                        </div>

                        <!-- Import Excel Form -->
                        <div class="tab-pane fade" id="excel" role="tabpanel" aria-labelledby="excel-tab">
                          <form action="{{ route('monitoring-kegiatan.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Download Format Excel Link -->
                            <div class="mt-3">
                              <label class="form-label">Download Format Excel</label><br>
                              <a href="{{ route('monitoring-kegiatan.download-format') }}" class="btn btn-link">Download Format Excel</a>
                            </div>

                            <!-- Upload Excel File -->
                            <div class="mt-3"> <!-- Add margin-top to create distance -->
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
              <!-- End Modal -->

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

  <script src="{{ asset('assets/js/monitoring-kegiatan.js') }}"></script>

  <!-- Memuat jQuery dari CDN -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var currentPage = 1;

      function applyFilters(page = 1) {
        const search = document.getElementById("searchInput").value;
        const bulan = document.getElementById("filter_bulan").value;
        const tahun = document.getElementById("filter_tahun").value;
        const perPageSelect = document.getElementById("per_page");
        const perPage = perPageSelect ? perPageSelect.value : '{{ request("per_page",10) }}';

        $.ajax({
          url: "{{ route('monitoring-kegiatan') }}",
          method: 'GET',
          data: {
            search: search,
            filter_bulan: bulan,
            filter_tahun: tahun,
            per_page: perPage,
            page: page
          },
          success: function(response) {
            const tbody = document.querySelector("#dataTable tbody");
            if (tbody) {
              tbody.innerHTML = response.table;
            }
            document.getElementById("paginationContainer").innerHTML = response.paginationHtml;
            const showingTextElem = document.getElementById('showingText');
            if (showingTextElem) {
              showingTextElem.textContent = response.showingText;
            }
            currentPage = page;
          },
          error: function(xhr) {
            console.error('Gagal memuat data:', xhr);
          }
        });
      }

      let debounceTimeout;
      document.getElementById("searchInput").addEventListener("keyup", function() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
          applyFilters(1);
        }, 300);
      });

      document.getElementById("filter_bulan").addEventListener("change", function() {
        applyFilters(1);
      });

      document.getElementById("filter_tahun").addEventListener("change", function() {
        applyFilters(1);
      });

      const perPageSelect = document.getElementById("per_page");
      if (perPageSelect) {
        perPageSelect.addEventListener("change", function() {
          applyFilters(1);
        });
      }

      document.getElementById('paginationContainer').addEventListener('click', function(e) {
        if (e.target.classList.contains('page-link')) {
          e.preventDefault();
          const page = e.target.getAttribute('data-page');
          if (page && page != currentPage) {
            applyFilters(parseInt(page));
          }
        }
      });

      applyFilters(currentPage);
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