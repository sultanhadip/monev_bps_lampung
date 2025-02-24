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
          <li class="breadcrumb-item"><a href="{{ route('index') }}" style="font-size: 18px;">Penilaian</a></li>
        </ol>
      </nav>
    </div>
    <!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Penilaian Kinerja</h5>

              <!-- Table with hoverable rows and horizontal scroll -->
              <div style="overflow-x: auto;">
                <div class="d-flex justify-content-end mb-3">
                  <button id="generateButton" class="btn btn-primary">Generate</button>
                </div>

                <table class="table table-hover table-bordered text-center mb-4" id="dataTable">
                  <thead>
                    <tr>
                      <th scope="col">Nomor</th>
                      <th scope="col">Satuan Kerja</th>
                      <th scope="col">Periode</th>
                      <th scope="col">Nilai</th>
                      <th scope="col">Peringkat</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($penilaian as $key => $nilai)
                    <tr>
                      <th scope="row">{{ $key + 1 }}</th>
                      <td>{{ $nilai->satuanKerja->nama_satuan_kerja }}</td> <!-- Menampilkan nama satuan kerja -->
                      <td>{{ $nilai->periode_kinerja }}</td>
                      <td>{{ $nilai->nilai_kinerja }}</td>
                      <td>{{ $nilai->peringkat }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>

              </div>
              <!-- End Table with hoverable rows and horizontal scroll -->

              <!-- Records per page and Pagination -->
              <div class="d-flex justify-content-between align-items-center">
                <div class="me-3">
                  <span>Showing {{ $penilaian->firstItem() }}-{{ $penilaian->lastItem() }} of {{ $penilaian->total() }} records</span>
                </div>

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
                      @if($penilaian->onFirstPage())
                      <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                      </li>
                      @else
                      <li class="page-item">
                        <a class="page-link" href="{{ $penilaian->previousPageUrl() }}">Previous</a>
                      </li>
                      @endif

                      @foreach($penilaian->getUrlRange(1, $penilaian->lastPage()) as $page => $url)
                      <li class="page-item {{ $penilaian->currentPage() == $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                      </li>
                      @endforeach

                      @if($penilaian->hasMorePages())
                      <li class="page-item">
                        <a class="page-link" href="{{ $penilaian->nextPageUrl() }}">Next</a>
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

  <!-- Include jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    $(document).ready(function() {
      // Ketika pilihan jumlah records per page diubah
      $("#recordsPerPage").change(function() {
        let perPage = $(this).val();
        window.location.href = "{{ url()->current() }}?recordsPerPage=" + perPage;
      });

      $("#generateButton").click(function() {
        console.log("Generate button clicked");
        if (confirm("Apakah Anda yakin ingin mengenerate nilai kinerja?")) {
          $.ajax({
            url: '{{ route("penilaian.generate") }}', // Blade route() disuntikkan dengan benar di sini
            type: "POST",
            data: {
              _token: "{{ csrf_token() }}", // CSRF token disuntikkan dengan benar
            },
            success: function(response) {
              console.log("Response:", response);
              alert(response.message);
              location.reload();
            },
            error: function(xhr) {
              let errorMessage = xhr.responseJSON ?
                xhr.responseJSON.message :
                "Terjadi kesalahan saat generate.";
              console.error("Error:", errorMessage);
              alert(errorMessage);
            },
          });
        }
      });
    });
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
</body>

</html>