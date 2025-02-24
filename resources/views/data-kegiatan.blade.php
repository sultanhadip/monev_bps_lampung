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
          <li class="breadcrumb-item active" style="font-size: 18px;"><a href="{{ route('data-kegiatan') }}">Data Kegiatan</a></li>
        </ol>
      </nav>
    </div>
    <!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Data Kegiatan Statistik</h5>

              <!-- Search and Add Button -->
              <div class="d-flex justify-content-between mb-3">
                <!-- Search Bar -->
                <input type="text" class="form-control w-25" id="searchInput" placeholder="Cari Kegiatan">

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
                    <th scope="col">Kode Kegiatan</th>
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
                  <span>Showing {{ $from }}-{{ $to }} of {{ $totalRecords }} records</span>
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
              <!-- End Pagination -->

              <!-- Modal Tambah Data Kegiatan -->
              <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addModalLabel">Tambah Data Kegiatan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- Form tambah data kegiatan -->
                      <form action="{{ route('datakegiatan.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                          <label for="kode_tim" class="form-label">Tim Kerja</label>
                          <select class="form-select" id="kode_tim" name="kode_tim" required>
                            <option value="">Pilih Tim Kerja</option>
                            @foreach ($timkerja as $tim)
                            <option value="{{ $tim->id }}">{{ $tim->nama_tim }}</option>
                            @endforeach
                          </select>
                        </div>

                        <div class="mb-3">
                          <label for="objek_kegiatan" class="form-label">Objek Kegiatan</label>
                          <select class="form-select" id="objek_kegiatan" name="objek_kegiatan" required>
                            <option value="">Pilih Objek Kegiatan</option>
                            <option value="Rumah Tangga">Rumah Tangga</option>
                            <option value="Usaha">Usaha</option>
                            <option value="Lainnya">Lainnya</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label for="periode_kegiatan" class="form-label">Periode Kegiatan</label>
                          <select class="form-select" id="periode_kegiatan" name="periode_kegiatan" required data-bs-menu-direction="down">
                            <option value="">Pilih Periode Kegiatan</option>
                            <option value="Bulanan">Bulanan</option>
                            <option value="Triwulan">Triwulan</option>
                            <option value="Semesteran">Semesteran</option>
                            <option value="Tahunan">Tahunan</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                          <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" required>
                        </div>
                        <div class="mb-3">
                          <label for="kode_kegiatan" class="form-label">Kode Kegiatan</label>
                          <input type="text" class="form-control" id="kode_kegiatan" name="kode_kegiatan" required>
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
      // Ambil URL dari elemen HTML (pastikan elemen ini ada pada halaman)
      var url = $('#ajax-url').data('url'); // URL untuk permintaan AJAX

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
            per_page: recordsPerPage // Kirim jumlah records per halaman ke server
          },
          success: function(response) {
            // Perbarui tabel dengan data yang diterima
            $('#dataTable tbody').html(response.data); // Update tabel dengan data yang baru

            // Update informasi jumlah record yang ditampilkan
            $(".me-3 span").text(`Showing ${response.from}-${response.to} of ${response.total} records`);

            // Update pagination
            var pagination = $('.pagination');
            pagination.html('');

            // Handle Previous and Next buttons
            if (response.prev_page_url) {
              pagination.append(`<li class="page-item"><a class="page-link" href="#" onclick="changePage(${response.current_page - 1})">Previous</a></li>`);
            }

            // Generate page number links
            response.links.forEach(function(link) {
              var activeClass = link.active ? 'active' : '';
              pagination.append(`<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="changePage(${link.label})">${link.label}</a></li>`);
            });

            if (response.next_page_url) {
              pagination.append(`<li class="page-item"><a class="page-link" href="#" onclick="changePage(${response.current_page + 1})">Next</a></li>`);
            }
          }
        });
      });
    });

    // Handling the change in "Records per page"
    document.getElementById('recordsPerPage').addEventListener('change', function() {
      var recordsPerPage = this.value;
      var url = new URL(window.location.href);
      url.searchParams.set('per_page', recordsPerPage); // Update parameter records per page in URL
      window.location.href = url.toString(); // Redirect to the updated URL
    });

    // Function to handle page change in pagination
    function changePage(pageNumber) {
      var search = document.getElementById('searchInput').value;
      var recordsPerPage = document.getElementById('recordsPerPage').value;
      fetch(`/monitoring-kegiatan?search=${search}&per_page=${recordsPerPage}&page=${pageNumber}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
          },
        })
        .then(response => response.json())
        .then(data => {
          // Update table content with new data
          updateTable(data);
        });
    }

    function updateTable(data) {
      let tbody = document.querySelector('#dataTable tbody');
      tbody.innerHTML = ''; // Clear existing table content

      data.data.forEach((item, index) => {
        let tr = document.createElement('tr');
        tr.innerHTML = `
        <td>${index + 1}</td>
        <td>${item.timkerja.nama_tim || 'N/A'}</td>
        <td><a href="/monitoring-kegiatan/${item.id}">${item.datakegiatan.nama_kegiatan}</a></td>
        <td>${item.periode_kegiatan || '-'}</td>
        <td>${item.target || '0'}</td>
        <td>${item.realisasi || '0'}</td>
        <td>${item.persentase}</td>
        <td>${item.waktu_kegiatan || '-'}</td>
        <td>${item.status}</td>
      `;
        tbody.appendChild(tr);
      });

      // Update information about the records
      document.querySelector(".me-3 span").textContent = `Showing ${data.from}-${data.to} of ${data.total} records`;

      // Update pagination links
      let pagination = document.querySelector('.pagination');
      pagination.innerHTML = '';

      if (data.prev_page_url) {
        let prevPage = document.createElement('li');
        prevPage.className = 'page-item';
        prevPage.innerHTML = `<a class="page-link" href="#" onclick="changePage(${data.current_page - 1})">Previous</a>`;
        pagination.appendChild(prevPage);
      }

      data.links.forEach(link => {
        let pageItem = document.createElement('li');
        pageItem.className = `page-item ${link.active ? 'active' : ''}`;
        pageItem.innerHTML = `<a class="page-link" href="#" onclick="changePage(${link.label})">${link.label}</a>`;
        pagination.appendChild(pageItem);
      });

      if (data.next_page_url) {
        let nextPage = document.createElement('li');
        nextPage.className = 'page-item';
        nextPage.innerHTML = `<a class="page-link" href="#" onclick="changePage(${data.current_page + 1})">Next</a>`;
        pagination.appendChild(nextPage);
      }
    }
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

  @if ($errors->has('kode_kegiatan'))
  <script>
    Swal.fire({
      icon: 'error',
      text: 'Kode Kegiatan sudah digunakan, masukkan kode lain',
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