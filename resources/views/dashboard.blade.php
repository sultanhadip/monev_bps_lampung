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
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet" />

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet" />

  <style>
    #columnChart {
      display: block !important;
    }
  </style>
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
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="font-size: 18px;">Dashboard</a></li>
        </ol>
      </nav>
    </div>
    <!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">
        <!-- Left side columns -->
        <div class="col-lg-8">
          <div class="row text-center">
            <!-- Target Card -->
            <div class="col-xxl-3 col-md-3 d-flex">
              <div class="card info-card sales-card flex-fill">
                <div class="card-body p-2"> <!-- Padding dikurangi menjadi p-2 -->
                  <p class="card-title fs-6 small">Target</p>
                  <div class="d-flex align-items-center justify-content-center">
                    <div class="text-center">
                      <h6 class="fs-5" id="target">-</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Realisasi Card -->
            <div class="col-xxl-3 col-md-3 d-flex">
              <div class="card info-card sales-card flex-fill">
                <div class="card-body p-2"> <!-- Padding dikurangi menjadi p-2 -->
                  <p class="card-title fs-6 small">Realisasi</p>
                  <div class="d-flex align-items-center justify-content-center">
                    <div class="text-center">
                      <h6 class="fs-5" id="realisasi">-</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- % Tertinggi Card -->
            <div class="col-xxl-3 col-md-3 d-flex">
              <div class="card info-card sales-card flex-fill">
                <div class="card-body p-2"> <!-- Padding dikurangi menjadi p-2 -->
                  <p class="card-title fs-6 small">% Tertinggi</p>
                  <div class="d-flex align-items-center justify-content-center">
                    <div class="text-center">
                      <h6 class="fs-5" id="tertinggi">-</h6>
                    </div>
                  </div>
                  <span class="text-muted small fs-7" id="tertinggi-nama">-</span>
                </div>
              </div>
            </div>

            <!-- % Terendah Card -->
            <div class="col-xxl-3 col-md-3 d-flex">
              <div class="card info-card sales-card flex-fill">
                <div class="card-body p-2"> <!-- Padding dikurangi menjadi p-2 -->
                  <p class="card-title fs-6 small">% Terendah</p>
                  <div class="d-flex align-items-center justify-content-center">
                    <div class="text-center">
                      <h6 class="fs-5" id="terendah">-</h6>
                    </div>
                  </div>
                  <span class="text-muted small fs-7" id="terendah-nama">-</span>
                </div>
              </div>
            </div>

            <div class="col-lg-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Realisasi Menurut Satuan Kerja</h5>

                  <!-- Column Chart -->
                  <div id="columnChart"></div>

                  <script>
                    // Initialize chart with no data
                    let chart = new ApexCharts(document.querySelector("#columnChart"), {
                      series: [{
                        name: "Target",
                        data: [],
                      }, {
                        name: "Realisasi",
                        data: [],
                      }],
                      chart: {
                        type: "bar",
                        height: 350,
                      },
                      xaxis: {
                        categories: [],
                        title: {
                          text: "Satuan Kerja",
                        },
                      },
                      yaxis: {
                        title: {
                          text: "Sampel",
                        },
                      },
                    });

                    chart.render();

                    // Function to update chart after data is retrieved
                    function updateChart(targetData, realisasiData, categories) {
                      chart.updateOptions({
                        series: [{
                          name: "Target",
                          data: targetData,
                        }, {
                          name: "Realisasi",
                          data: realisasiData,
                        }],
                        xaxis: {
                          categories: categories,
                        }
                      });
                    }

                    $('#btn-tampilkan').click(function() {
                      const kodeKegiatan = $('#nama_kegiatan').val();
                      if (!kodeKegiatan) {
                        alert('Silakan pilih kegiatan yang valid.');
                        return;
                      }
                      const waktuKegiatan = $('#waktu_kegiatan').val();

                      $.ajax({
                        url: '{{ route("get.filteredData") }}',
                        type: 'GET',
                        data: {
                          kode_kegiatan: kodeKegiatan,
                          waktu_kegiatan: waktuKegiatan,
                        },
                        success: function(response) {
                          // Update data in UI
                          $('#target').text(response.target || '-');
                          $('#realisasi').text(response.realisasi || '-');
                          $('#tertinggi').text(response.tertinggi.persentase || '-');
                          $('#tertinggi-nama').text(response.tertinggi.nama || '-');
                          $('#terendah').text(response.terendah.persentase || '-');
                          $('#terendah-nama').text(response.terendah.nama || '-');

                          // Update chart with new data
                          updateChart(response.chartTargetData, response.chartRealisasiData, response.chartCategories);
                        },
                        error: function(xhr) {
                          alert('Terjadi kesalahan saat mengambil data: ' + xhr.responseText);
                        },
                      });
                    });
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End Left side columns -->

        <!-- Right side columns -->
        <div class="col-lg-4">
          <!-- Filter Form -->
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0"><i class="bi bi-funnel-fill"></i> Filter</h5>
            </div>
            <div class="card-body">
              <form id="filter-form">
                <!-- Dropdown Tim -->
                <div class="mb-3">
                  <label for="tim" class="form-label">Pilih Tim</label>
                  <select class="form-select" id="tim" name="tim">
                    <option value="">Pilih Tim</option>
                    @foreach ($timNames as $id => $namaTim)
                    <option value="{{ $id }}">{{ $namaTim }}</option>
                    @endforeach
                  </select>
                </div>

                <!-- Dropdown Objek -->
                <div class="mb-3">
                  <label for="objek" class="form-label">Pilih Objek</label>
                  <select class="form-select" id="objek" name="objek">
                    <option value="">Pilih Objek</option>
                  </select>
                </div>

                <!-- Dropdown Periode -->
                <div class="mb-3">
                  <label for="periode" class="form-label">Pilih Periode</label>
                  <select class="form-select" id="periode" name="periode">
                    <option value="">Pilih Periode</option>
                  </select>
                </div>

                <!-- Dropdown Nama Kegiatan -->
                <div class="mb-3">
                  <label for="nama_kegiatan" class="form-label">Pilih Nama Kegiatan</label>
                  <select class="form-select" id="nama_kegiatan" name="nama_kegiatan">
                    <option value="">Pilih Kegiatan</option>
                    @foreach ($monitoringData as $data)
                    <option value="{{ $data->kode_kegiatan }}">{{ $data->nama_kegiatan }}</option>
                    @endforeach
                  </select>
                </div>

                <!-- Dropdown Waktu Kegiatan -->
                <div class="mb-3" id="waktu-kegiatan-container" style="display: none;">
                  <label for="waktu_kegiatan" class="form-label">Pilih Waktu Kegiatan</label>
                  <select class="form-select" id="waktu_kegiatan" name="waktu_kegiatan">
                    <option value="">Pilih Waktu Kegiatan</option>
                  </select>
                </div>

                <div class="d-grid">
                  <button type="button" id="btn-tampilkan" class="btn btn-primary">Tampilkan</button>
                </div>
              </form>
            </div>
          </div>
          <!-- End Filter -->
        </div>
        <!-- End Right side columns -->
      </div>
    </section>
  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  @include('layouts.footer')

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- AJAX Script -->
  <script>
    $(document).ready(function() {
      // Fungsi untuk memperbarui dropdown Objek berdasarkan pilihan Tim
      function updateObjek() {
        const tim = $('#tim').val();
        if (!tim) {
          $('#objek').html('<option value="">Pilih objek</option>');
          $('#periode').html('<option value="">Pilih periode</option>');
          $('#nama_kegiatan').html('<option value="">Pilih kegiatan</option>');
          $('#periode-waktu-container').hide(); // Sembunyikan periode waktu
          return;
        }

        $.ajax({
          url: '{{ route("get.objek") }}',
          type: 'GET',
          data: {
            tim_id: tim
          },
          success: function(response) {
            const options = response
              .map((objek) => `<option value="${objek}">${objek}</option>`)
              .join('');
            $('#objek').html('<option value="">Pilih objek</option>' + options);
            $('#periode').html('<option value="">Pilih periode</option>');
            $('#nama_kegiatan').html('<option value="">Pilih kegiatan</option>');
            $('#periode-waktu-container').hide(); // Sembunyikan periode waktu
          },
          error: function(xhr) {
            console.error('Error fetching objek:', xhr.responseText);
            alert('Gagal memuat daftar objek.');
          },
        });
      }

      // Fungsi untuk memperbarui dropdown Periode berdasarkan pilihan Objek
      function updatePeriode() {
        const tim = $('#tim').val();
        const objek = $('#objek').val();

        if (!tim || !objek) {
          $('#periode').html('<option value="">Pilih periode</option>');
          $('#nama_kegiatan').html('<option value="">Pilih kegiatan</option>');
          $('#periode-waktu-container').hide(); // Sembunyikan periode waktu
          return;
        }

        $.ajax({
          url: '{{ route("get.periode") }}',
          type: 'GET',
          data: {
            tim_id: tim,
            objek: objek
          },
          success: function(response) {
            const options = response
              .map((periode) => `<option value="${periode}">${periode}</option>`)
              .join('');
            $('#periode').html('<option value="">Pilih periode</option>' + options);
            $('#nama_kegiatan').html('<option value="">Pilih kegiatan</option>');
            $('#periode-waktu-container').hide(); // Sembunyikan periode waktu
          },
          error: function(xhr) {
            console.error('Error fetching periode:', xhr.responseText);
            alert('Gagal memuat daftar periode.');
          },
        });
      }

      // Fungsi untuk memperbarui dropdown Nama Kegiatan berdasarkan pilihan Periode
      function updateNamaKegiatan() {
        const tim = $('#tim').val();
        const objek = $('#objek').val();
        const periode = $('#periode').val();

        if (!tim || !objek || !periode) {
          $('#nama_kegiatan').html('<option value="">Pilih kegiatan</option>');
          $('#periode-waktu-container').hide(); // Sembunyikan periode waktu
          return;
        }

        $.ajax({
          url: '{{ route("get.namaKegiatan") }}',
          type: 'GET',
          data: {
            tim_id: tim,
            objek: objek,
            periode: periode
          },
          success: function(response) {
            if (Object.keys(response).length === 0) {
              $('#nama_kegiatan').html('<option value="">Tidak ada kegiatan yang tersedia</option>');
            } else {
              const options = Object.entries(response)
                .map(([kode, nama]) => `<option value="${kode}">${nama}</option>`)
                .join('');
              $('#nama_kegiatan').html('<option value="">Pilih kegiatan</option>' + options);
            }
            $('#periode-waktu-container').hide(); // Sembunyikan periode waktu
          },
          error: function(xhr) {
            console.error('Error fetching nama kegiatan:', xhr.responseText);
            alert('Gagal memuat daftar kegiatan.');
          },
        });
      }

      // Fungsi untuk memperbarui dropdown Waktu Kegiatan berdasarkan Nama Kegiatan
      function updatePeriodeWaktu() {
        const kodeKegiatan = $('#nama_kegiatan').val();

        if (!kodeKegiatan) {
          $('#waktu-kegiatan-container').hide(); // Sembunyikan dropdown waktu kegiatan jika tidak ada nama kegiatan
          return;
        }

        $.ajax({
          url: '{{ route("get.periodeKegiatan") }}',
          type: 'GET',
          data: {
            kode_kegiatan: kodeKegiatan
          },
          success: function(response) {
            let options = '<option value="">Pilih Waktu Kegiatan</option>';

            // Tambahkan opsi berdasarkan periode yang sesuai
            if (response.waktu_kegiatan && response.waktu_kegiatan.length > 0) {
              response.waktu_kegiatan.forEach(waktu => {
                options += `<option value="${waktu}">${waktu}</option>`;
              });
            }

            // Update dropdown waktu kegiatan
            $('#waktu_kegiatan').html(options);

            // Tampilkan dropdown jika ada data, sembunyikan jika tidak ada
            if (options === '<option value="">Pilih Waktu Kegiatan</option>') {
              $('#waktu-kegiatan-container').hide();
            } else {
              $('#waktu-kegiatan-container').show(); // Tampilkan dropdown
            }
          },
          error: function(xhr) {
            console.error('Error fetching periode waktu:', xhr.responseText);
            alert('Gagal memuat periode waktu. Silakan coba lagi.');
          }
        });
      }

      // Event listener untuk perubahan pada dropdown Tim, Objek, Periode, dan Nama Kegiatan
      $('#tim').change(updateObjek);
      $('#objek').change(updatePeriode);
      $('#periode').change(updateNamaKegiatan);
      $('#nama_kegiatan').change(updatePeriodeWaktu);

      $('#btn-tampilkan').click(function() {
        const kodeKegiatan = $('#nama_kegiatan').val();
        if (!kodeKegiatan) {
          alert('Silakan pilih kegiatan yang valid.');
          return;
        }
        const waktuKegiatan = $('#waktu_kegiatan').val();

        $.ajax({
          url: '{{ route("get.filteredData") }}',
          type: 'GET',
          data: {
            kode_kegiatan: kodeKegiatan,
            waktu_kegiatan: waktuKegiatan,
          },
          success: function(response) {
            // Update data di UI
            $('#target').text(response.target || '-');
            $('#realisasi').text(response.realisasi || '-');
            $('#tertinggi').text(response.tertinggi.persentase || '-');
            $('#tertinggi-nama').text(response.tertinggi.nama || '-');
            $('#terendah').text(response.terendah.persentase || '-');
            $('#terendah-nama').text(response.terendah.nama || '-');

            // Update chart dengan data yang baru
            updateChart(response.chartTargetData, response.chartRealisasiData, response.chartCategories);
          },
          error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data: ' + xhr.responseText);
          },
        });
      });

      // Fungsi untuk menghancurkan chart yang ada
      function destroyChart() {
        const chart = ApexCharts.getChartByID('columnChart');
        if (chart) {
          chart.destroy(); // Menghancurkan grafik yang ada
          console.log('Chart destroyed');
        }
      }

      // Fungsi untuk memperbarui grafik dengan data baru
      function updateChart(targetData, realisasiData, categories) {
        // Hancurkan grafik lama sebelum render yang baru
        destroyChart();

        // Pastikan kontainer grafik sudah siap untuk menerima grafik baru
        const chartContainer = document.querySelector('#columnChart');
        chartContainer.innerHTML = ''; // Clear any existing chart before rendering a new one

        // Render ulang chart dengan data baru
        const chart = new ApexCharts(chartContainer, {
          series: [{
              name: "Target",
              data: targetData, // Gunakan data target yang diterima dari server
            },
            {
              name: "Realisasi",
              data: realisasiData, // Gunakan data realisasi yang diterima dari server
            },
          ],
          chart: {
            type: "bar",
            height: 350,
          },
          xaxis: {
            categories: categories // Gunakan kategori yang diterima dari server
          },
          yaxis: {
            title: {
              text: "Sampel", // Y-axis label as "Sampel"
            },
          },
          plotOptions: {
            bar: {
              horizontal: false,
              columnWidth: "55%",
            },
          },
        });

        chart.render(); // Render ulang grafik dengan data yang baru
        console.log('New chart rendered');
      }

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