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
              <h5 class="card-title">Monitoring Kegiatan</h5>

              <!-- Search and Add Button -->
              <div class="d-flex justify-content-between mb-3">
                <!-- Search Bar -->
                <input type="text" class="form-control w-25" id="searchInput" placeholder="Cari Tim Kerja">

                <!-- Add Button -->
                <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addModal">
                  <i class="bi bi-plus me-1 text-white"></i> Tambah
                </button>
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
                    @foreach ($monitoringKegiatan as $index => $item)
                    <tr>
                      <td scope="row">{{ $index + 1 }}</td>
                      <td>{{ $item->timkerja->nama_tim ?? 'N/A' }}</td> <!-- Nama Tim Kerja -->
                      <td>
                        <a href="{{ route('detail-monitoring-kegiatan', ['id' => $item->id]) }}">
                          {{ $item->datakegiatan->nama_kegiatan }}
                        </a>
                      </td>
                      <td>{{ $item->periode_kegiatan ?? '-' }}</td> <!-- Menampilkan Periode -->
                      <td>{{ $item->target ?? '0' }}</td>
                      <td>{{ $item->realisasi ?? '0' }}</td>
                      <td>{{ $item->persentase }}</td>
                      <td>{{ $item->waktu_kegiatan ?? '-' }}</td>
                      <td>
                        @if ($item->status === 'SELESAI')
                        <span class="badge bg-success text-white">{{ $item->status }}</span>
                        @elseif ($item->status === 'BELUM DIMULAI')
                        <span class="badge bg-danger text-white">{{ $item->status }}</span>
                        @elseif ($item->status === 'ON PROGRESS')
                        <span class="badge bg-warning text-white">{{ $item->status }}</span>
                        @else
                        <span class="badge bg-secondary text-white">{{ $item->status }}</span>
                        @endif
                      </td>
                      @if ($canAccessVerifikasi)
                      <td>
                        <!-- Container for the buttons -->
                        <div class="d-flex">
                          <!-- Edit Button -->
                          <button
                            class="btn btn-sm btn-icon btn-white text-primary border-0"
                            data-bs-toggle="modal"
                            data-bs-target="#editModal{{ $item->id }}">
                            <i class="bi bi-pencil-square fs-6"></i>
                          </button>

                          <!-- Delete Button -->
                          <form action="{{ route('monitoringkegiatan.destroy', $item->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button
                              type="submit"
                              class="btn btn-sm btn-icon btn-white text-danger border-0">
                              <i class="bi bi-trash fs-6"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                      @endif
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered"> <!-- Modal Centered untuk penempatan yang lebih rapi -->
                        <div class="modal-content">
                          <form action="{{ route('monitoringkegiatan.update', $item->id) }}" method="POST">
                            @csrf
                            @method('PUT') <!-- Pastikan menggunakan method PUT -->

                            <div class="modal-header">
                              <h5 class="modal-title" id="editModalLabel">Edit Kegiatan Monitoring</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                              <!-- Kode Tim -->
                              <div class="mb-3">
                                <label for="kode_tim_{{ $item->id }}" class="form-label">Tim Kerja</label>
                                <select class="form-select" id="kode_tim_{{ $item->id }}" name="kode_tim" required>
                                  <option value="" disabled>Pilih Tim Kerja</option>
                                  @foreach($timkerja as $tim)
                                  <option value="{{ $tim->id }}" {{ $item->kode_tim == $tim->id ? 'selected' : '' }}>
                                    {{ $tim->nama_tim }}
                                  </option>
                                  @endforeach
                                </select>
                              </div>

                              <!-- Kode Kegiatan -->
                              <div class="mb-3">
                                <label for="kode_kegiatan_{{ $item->id }}" class="form-label">Nama Kegiatan</label>
                                <select class="form-select" id="kode_kegiatan_{{ $item->id }}" name="kode_kegiatan" required>
                                  <option value="" disabled>Pilih Kegiatan</option>
                                  @foreach($datakegiatan as $kegiatan)
                                  <option value="{{ $kegiatan->id }}" {{ $item->kode_kegiatan == $kegiatan->id ? 'selected' : '' }}>
                                    {{ $kegiatan->nama_kegiatan }}
                                  </option>
                                  @endforeach
                                </select>
                              </div>

                              <!-- Tahun Kegiatan -->
                              <div class="mb-3">
                                <label for="tahun_kegiatan_{{ $item->id }}" class="form-label">Tahun Kegiatan</label>
                                <input type="number" class="form-control" id="tahun_kegiatan_{{ $item->id }}" name="tahun_kegiatan" value="{{ old('tahun_kegiatan', $item->tahun_kegiatan) }}" required>
                              </div>

                              <!-- Waktu Mulai -->
                              <div class="mb-3">
                                <label for="waktu_mulai_{{ $item->id }}" class="form-label">Waktu Mulai</label>
                                <input type="date" class="form-control" id="waktu_mulai_{{ $item->id }}" name="waktu_mulai" value="{{ old('waktu_mulai', $item->waktu_mulai) }}" required>
                              </div>

                              <!-- Waktu Selesai -->
                              <div class="mb-3">
                                <label for="waktu_selesai_{{ $item->id }}" class="form-label">Waktu Selesai</label>
                                <input type="date" class="form-control" id="waktu_selesai_{{ $item->id }}" name="waktu_selesai" value="{{ old('waktu_selesai', $item->waktu_selesai) }}" required>
                              </div>

                              <!-- Satuan Kerja & Target Sampel -->
                              <div class="mb-3">
                                <label class="form-label">Satuan Kerja & Target Sampel</label>
                                @foreach ($satuankerja as $satker)
                                <div class="d-flex mb-2">
                                  <label class="me-3">{{ $satker->nama_satuan_kerja }}</label>
                                  <input type="number" class="form-control" name="target_sampel[{{ $satker->id }}]"
                                    value="{{ old('target_sampel.' . $satker->id, $item->targetRealisasiSatker->where('kode_satuan_kerja', $satker->id)->first()->target_satker ?? 0) }}"
                                    placeholder="Target Sampel" required style="width: 100%;">
                                </div>
                                @endforeach
                              </div>
                            </div>

                            <div class="modal-footer">
                              <button type="button" class="btn text-white" style="background-color: rgb(250, 82, 82);" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" class="btn btn-primary text-white">Simpan</button>
                            </div>
                          </form>

                        </div>
                      </div>
                    </div>
                    <!-- End Edit Modal -->

                    @endforeach
                  </tbody>
                </table>
              </div>
              <!-- End Table with hoverable rows and horizontal scroll -->

              <!-- Records per page and Pagination -->
              <div class="d-flex justify-content-between align-items-center">
                <!-- Showing Text -->
                <div class="me-3">
                  <span>Showing {{ $monitoringKegiatan->firstItem() }}-{{ $monitoringKegiatan->lastItem() }} of {{ $monitoringKegiatan->total() }} records</span>
                </div>

                <!-- Records Per Page Dropdown and Pagination at the right -->
                <div class="d-flex align-items-center ms-auto">
                  <!-- Records per page -->
                  <div class="me-3 d-flex align-items-center">
                    <span class="me-2">Records per page</span>
                    <form action="{{ route('monitoring-kegiatan') }}" method="GET" class="d-flex">
                      <select name="per_page" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', 10) == 15 ? 'selected' : '' }}>15</option>
                        <option value="20" {{ request('per_page', 10) == 20 ? 'selected' : '' }}>20</option>
                      </select>
                    </form>
                  </div>

                  <!-- Pagination -->
                  <nav>
                    <ul class="pagination m-0">
                      <li class="page-item {{ $monitoringKegiatan->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $monitoringKegiatan->previousPageUrl() }}">Previous</a>
                      </li>
                      @foreach ($monitoringKegiatan->getUrlRange(1, $monitoringKegiatan->lastPage()) as $page => $url)
                      <li class="page-item {{ $monitoringKegiatan->currentPage() == $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                      </li>
                      @endforeach
                      <li class="page-item {{ !$monitoringKegiatan->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $monitoringKegiatan->nextPageUrl() }}">Next</a>
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>
              <!-- End Pagination -->

              <!-- Modal Tambah Data Monitoring Kegiatan -->
              <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addModalLabel">Tambah Monitoring Kegiatan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('monitoring-kegiatan.store') }}" method="POST">
                        @csrf
                        <!-- Form Fields -->
                        <div class="mb-3">
                          <label for="kode_tim" class="form-label">Tim Kerja</label>
                          <select class="form-select" id="kode_tim" name="kode_tim" required>
                            <option value="" disabled selected>Pilih Tim Kerja</option>
                            @foreach($timkerja as $tim)
                            <option value="{{ $tim->id }}">{{ $tim->nama_tim }}</option>
                            @endforeach
                          </select>
                        </div>

                        <!-- Data Kegiatan (dikirim lewat data attributes) -->
                        <div id="datakegiatan" data-kegiatan="{{ json_encode($datakegiatan) }}" style="display: none;"></div>

                        <!-- Form Fields for Kegiatan, Tahun, Bulan, etc -->
                        <div class="mb-3" id="kode_kegiatan-container">
                          <label for="kode_kegiatan" class="form-label">Nama Kegiatan</label>
                          <select class="form-select" id="kode_kegiatan" name="kode_kegiatan" required>
                            <option value="" disabled selected>Pilih Kegiatan</option>
                            <!-- Opsi akan diisi lewat AJAX berdasarkan tim yang dipilih -->
                          </select>
                        </div>

                        <!-- Input tersembunyi untuk id_data_kegiatan -->
                        <input type="hidden" id="id_data_kegiatan" name="id_data_kegiatan">

                        <!-- Tahun (Selalu Ditampilkan) -->
                        <div class="mb-3">
                          <label for="tahun_kegiatan" class="form-label">Tahun Kegiatan</label>
                          <input type="number" class="form-control" id="tahun_kegiatan" name="tahun_kegiatan" required>
                        </div>

                        <!-- Bulan -->
                        <div class="mb-3" id="bulan-container" style="display: none;">
                          <label for="bulan" class="form-label">Bulan</label>
                          <input type="text" class="form-control" id="bulan" name="bulan">
                        </div>

                        <!-- Triwulan -->
                        <div class="mb-3" id="triwulan-container" style="display: none;">
                          <label for="triwulan" class="form-label">Triwulan</label>
                          <input type="text" class="form-control" id="triwulan" name="triwulan">
                        </div>

                        <!-- Semester -->
                        <div class="mb-3" id="semester-container" style="display: none;">
                          <label for="semester" class="form-label">Semester</label>
                          <input type="text" class="form-control" id="semester" name="semester">
                        </div>

                        <!-- Input Waktu Mulai dan Waktu Selesai -->
                        <div class="mb-3">
                          <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
                          <input type="date" class="form-control" id="waktu_mulai" name="waktu_mulai" required>
                        </div>
                        <div class="mb-3">
                          <label for="waktu_selesai" class="form-label">Waktu Selesai</label>
                          <input type="date" class="form-control" id="waktu_selesai" name="waktu_selesai" required>
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
                                <p class="d-inline-block mb-0">{{ $satker->nama_satuan_kerja }}</p>
                              </label>
                            </div>
                            @endforeach
                          </div>

                          <div id="target-sampel-container" class="mt-3"></div>
                        </div>

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

  <script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
      var keyword = this.value;
      window.location.href = "/monitoring-kegiatan?search=" + keyword + "&per_page={{ request('per_page', 10) }}";
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  @if (session('success'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: "{{ session('success') }}",
      showConfirmButton: true,
      timer: 3000
    });
  </script>
  @endif

  @if (session('error'))
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: "{{ session('error') }}",
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