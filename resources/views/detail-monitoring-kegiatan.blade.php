<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />

  <title>Montify</title>
  <meta content="" name="description" />
  <meta content="" name="keywords" />

  <!-- Favicons -->
  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon" />
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon" />

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect" />
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet" />

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet" /> <!-- Perbaikan pada style.css -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" /> <!-- Pastikan file style.css ada -->

  <!-- Memuat jQuery dari CDN -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Memuat Moment.js dari CDN -->
  <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

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
          <li class="breadcrumb-item" style="font-size: 18px;">Monitoring Kegiatan</li>
          <li class="breadcrumb-item active" style="font-size: 18px;"><a href="{{ route('detail-monitoring-kegiatan', ['id' => $monitoringKegiatan->id]) }}">Detail Monitoring Kegiatan</a></li>
        </ol>
      </nav>
    </div>
    <!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Informasi Kegiatan</h5>

              <div style="overflow-x: auto">
                <table class="table table-hover table-bordered" id="dataTable">
                  <tbody>
                    <tr>
                      <th scope="row">Nama Kegiatan</th>
                      <td>{{ $monitoringKegiatan->datakegiatan->nama_kegiatan ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <th scope="row">Tim Kerja</th>
                      <td>{{ $monitoringKegiatan->timkerja->nama_tim ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <th scope="row">Objek</th>
                      <td>{{ $monitoringKegiatan->datakegiatan->objek_kegiatan ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <th scope="row">Periode</th>
                      <td>{{ $monitoringKegiatan->datakegiatan->periode_kegiatan ?? 'N/A'}}</td>
                    </tr>
                    <tr>
                      <th scope="row">Batas Waktu</th>
                      <td>{{ \Carbon\Carbon::parse($monitoringKegiatan->waktu_selesai)->format('d-m-Y') ?? '-' }}</td>
                    </tr>
                  </tbody>
                </table>

              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabel Realisasi Kegiatan Menurut Wilayah -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Realisasi Kegiatan Menurut Wilayah</h5>

              <!-- Alert messages -->
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

              <div style="overflow-x: auto;">
                @can('canExport')
                <a href="{{ route('export-realisasi', ['id_kegiatan' => $monitoringKegiatan->id]) }}" class="btn btn-success mb-3">
                  <i class="fas fa-file-excel me-1"></i> Export
                </a>
                @endcan

                <table class="table table-hover table-bordered text-center" id="dataTable">
                  <thead>
                    <tr>
                      <th scope="col">Satuan Kerja</th>
                      <th scope="col">Target</th>
                      <th scope="col">Realisasi</th>
                      <th scope="col">Persentase</th>
                      <th scope="col">Tanggal Update</th>
                      @if ($canAccessPengajuanKeterangan)
                      <th scope="col">Pengajuan</th>
                      <th scope="col">Pesan</th>
                      @endif
                      @if ($canAccessVerifikasi)
                      <th scope="col">Verifikasi</th>
                      @endif
                      @if ($canAccessPengajuanKeterangan)
                      <th scope="col">Status</th>
                      @endif
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($targetRealisasiSatker as $index => $item)
                    <tr>
                      <td class="satuan-kerja text-primary" data-id="{{ $item->satuankerja->id ?? '' }}" style="cursor: pointer;">
                        {{ $item->satuankerja ? "[{$item->satuankerja->kode_satuan_kerja}] {$item->satuankerja->nama_satuan_kerja}" : 'N/A' }}
                      </td>

                      <td>{{ $item->target_satker ?? '-' }}</td>
                      <td>{{ $item->realisasi_satker ?? '-' }}</td>
                      <td>{{ $item->persentase ?? '-' }}</td>
                      <td>
                        {{ $item->latest_updated_at ?? '-' }}
                      </td>

                      @if ($canAccessPengajuanKeterangan)
                      <td>
                        <div class="d-flex align-items-center justify-content-center">
                          @php
                          // Ambil status update dari preTargetRealisasiSatker sesuai indeks loop
                          $status = strtolower($preTargetRealisasiSatker[$index]->pre_status ?? '');
                          @endphp

                          @if ($item->satuankerja->id == $userKodeSatuanKerja && $status !== 'menunggu verifikasi')
                          <button class="btn btn-sm btn-white text-primary border-0 btn-edit-realisasi"
                            data-bs-toggle="modal" data-bs-target="#addRealisasiModal"
                            data-id="{{ $item->id }}"
                            data-realisasi="{{ $item->realisasi_satker }}"
                            data-target="{{ $item->target_satker }}">
                            <i class="fas fa-edit fs-6"></i>
                          </button>

                          @else
                          <span class="text-muted"></span>
                          @endif
                        </div>
                      </td>

                      @endif

                      @if ($canAccessPengajuanKeterangan)
                      <td class="text-center">
                        @php
                        $pesan = $preTargetRealisasiSatker[$index]->pre_pesan ?? null;
                        $status = strtolower($preTargetRealisasiSatker[$index]->pre_status ?? '');
                        @endphp

                        @if ($pesan && $pesan !== '-')
                        <button
                          type="button"
                          class="btn btn-link p-0 pesan-info-btn"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          title="Lihat Pesan"
                          data-pesan="{{ e($pesan) }}">
                          <i class="bi bi-info-circle fs-5 text-primary"></i>
                        </button>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                      </td>

                      @endif
                      @if ($canAccessVerifikasi)
                      <td>
                        <div class="d-flex align-items-center justify-content-center">
                          @if ($preTargetRealisasiSatker[$index]->updateRealisasi->count() > 0)
                          @php
                          $status = $preTargetRealisasiSatker[$index]->pre_status;
                          @endphp

                          @if ($status == 'Menunggu Verifikasi')
                          <button class="btn btn-sm btn-white text-primary approve-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#approveProposalModal"
                            data-id="{{ $preTargetRealisasiSatker[$index]->updateRealisasi->first()->id }}"
                            data-id-target-realisasi="{{ $preTargetRealisasiSatker[$index]->updateRealisasi->first()->id_target_realisasi }}"
                            data-realisasi="{{ $preTargetRealisasiSatker[$index]->pre_realisasi_satker }}"
                            data-bukti="{{ asset('storage/' . $preTargetRealisasiSatker[$index]->pre_bukti_dukung) }}"
                            data-keterangan="{{ $preTargetRealisasiSatker[$index]->pre_keterangan }}">
                            <i class="bi bi-check-circle fs-5"></i>
                          </button>

                          @elseif ($status == 'diterima' || $status == 'ditolak')
                          <div class="badge bg-secondary text-white">Belum ada usulan</div>
                          @else
                          <div class="badge bg-secondary text-white">{{ $status }}</div>
                          @endif
                          @else
                          <div class="badge bg-secondary text-white">Belum ada usulan</div>
                          @endif
                        </div>
                      </td>
                      @endif

                      @if ($canAccessPengajuanKeterangan)
                      <td>
                        <div
                          @if (strtolower($preTargetRealisasiSatker[$index]->pre_status) == 'menunggu verifikasi')
                          class="badge bg-warning text-white"
                          @elseif (strtolower($preTargetRealisasiSatker[$index]->pre_status) == 'diterima')
                          class="badge bg-success text-white"
                          @elseif (strtolower($preTargetRealisasiSatker[$index]->pre_status) == 'ditolak')
                          class="badge bg-danger text-white"
                          @else
                          class="badge bg-secondary text-white small"
                          @endif
                          >
                          {{ $preTargetRealisasiSatker[$index]->pre_status ?? '-' }}
                        </div>

                      </td>
                      @endif
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <!-- Modal Riwayat Realisasi -->
              <div class="modal fade" id="infoSatuanKerjaModal" tabindex="-1" aria-labelledby="infoSatuanKerjaModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                  <div class="modal-content rounded-1">
                    <div class="modal-header">
                      <h5 class="modal-title" id="infoSatuanKerjaModalLabel">Riwayat Realisasi Kegiatan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- Nav Tabs -->
                      <ul class="nav nav-tabs" id="riwayatTab" role="tablist">
                        <li class="nav-item" role="presentation">
                          <button
                            class="nav-link active"
                            id="tabel-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#tabel"
                            type="button"
                            role="tab"
                            aria-controls="tabel"
                            aria-selected="true">
                            Tabel
                          </button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button
                            class="nav-link"
                            id="grafik-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#grafik"
                            type="button"
                            role="tab"
                            aria-controls="grafik"
                            aria-selected="false">
                            Grafik
                          </button>
                        </li>
                      </ul>

                      <!-- Tab Panes -->
                      <div class="tab-content" id="riwayatTabContent">
                        <!-- Bagian Tabel -->
                        <div
                          class="tab-pane fade show active p-3"
                          id="tabel"
                          role="tabpanel"
                          aria-labelledby="tabel-tab">

                          <!-- Dropdown bulan, selebar penuh -->
                          <div class="mb-3" style="width: 100%;">
                            <label for="monthSelect" class="form-label mb-2">Pilih Bulan</label>
                            <select id="monthSelect" class="form-select w-100">
                              <option value="">Pilih Bulan</option>
                              <!-- Data bulan akan dimuat secara dinamis melalui JavaScript -->
                            </select>
                          </div>

                          <!-- Tombol Export di kanan -->
                          <a id="export-riwayat" href="#" class="btn btn-success mb-3" style="white-space: nowrap;">
                            <i class="fas fa-file-excel me-1"></i> Export
                          </a>

                          <table class="table table-bordered table-striped text-center mb-3">
                            <thead>
                              <tr>
                                <th>Tanggal</th>
                                <th>Realisasi</th>
                                <th>Akumulasi</th>
                                <th>Persentase</th>
                              </tr>
                            </thead>
                            <tbody id="riwayatTableBody">
                              <!-- Data Tabel akan dimuat melalui JavaScript -->
                            </tbody>
                          </table>

                          <!-- Pagination -->
                          <div class="d-flex justify-content-between align-items-center">
                            <div class="small">
                              <span id="recordInfo"></span>
                            </div>
                            <div class="d-flex align-items-center small">
                              <div class="d-flex align-items-center ms-auto me-3">
                                <label for="recordsPerPage" class="me-2 mb-0">Records per page:</label>
                                <select id="recordsPerPage" class="form-select form-select-sm w-auto text-center">
                                  <option value="5">5</option>
                                  <option value="10" selected>10</option>
                                  <option value="25">25</option>
                                </select>
                              </div>
                              <button id="previousPage" class="btn btn-sm btn-light" disabled>Previous</button>
                              <div id="pageNumbers" class="d-inline-flex mx-0"></div>
                              <button id="nextPage" class="btn btn-sm btn-light" disabled>Next</button>
                            </div>
                          </div>
                        </div>

                        <!-- Bagian Grafik -->
                        <div
                          class="tab-pane fade p-3"
                          id="grafik"
                          role="tabpanel"
                          aria-labelledby="grafik-tab">

                          <!-- Grafik Realisasi -->
                          <canvas id="riwayatChart" style="max-height: 400px;"></canvas>
                        </div>
                      </div> <!-- End tab-content -->
                    </div>

                    <div class="modal-footer border-top-0">
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modal Form Update Realisasi -->
              <div class="modal fade" id="addRealisasiModal" tabindex="-1" aria-labelledby="addRealisasiModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content rounded-1">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addRealisasiModalLabel">Update Realisasi Kegiatan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('update-realisasi') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id_target_realisasi" id="id_target_realisasi"> <!-- ID target_realisasi yang ingin diupdate -->
                        <div class="mb-3">
                          <label for="realisasi" class="form-label">Realisasi</label>
                          <input type="number" class="form-control" id="realisasi" name="realisasi_satker" required>
                        </div>

                        <div class="mb-3">
                          <label for="bukti_dukung" class="form-label">Bukti Dukung</label>
                          <input type="file" class="form-control" id="bukti_dukung" name="bukti_dukung_realisasi" accept=".pdf" required>
                        </div>
                        <div class="mb-3">
                          <label for="keterangan" class="form-label">Keterangan</label>
                          <textarea class="form-control" id="keterangan" name="keterangan"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modal Pesan Informasi -->
              <div class="modal fade" id="pesanInfoModal" tabindex="-1" aria-labelledby="pesanInfoModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content rounded-1">
                    <div class="modal-header">
                      <h5 class="modal-title" id="pesanInfoModalLabel">Pesan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body" id="pesanInfoContent">
                      <!-- Isi pesan akan dimasukkan di sini via JS -->
                    </div>
                    <div class="modal-footer border-top-0">
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modal Approve Realisasi -->
              <div class="modal fade" id="approveProposalModal" tabindex="-1" aria-labelledby="approveProposalModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content rounded-1">
                    <div class="modal-header">
                      <h5 class="modal-title" id="approveProposalModalLabel">Persetujuan Usulan Realisasi</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('approve-usulan') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_target_realisasi_approve" id="id_target_realisasi_approve">
                        <input type="hidden" name="id_update_realisasi" id="id_update_realisasi">

                        <!-- Usulan Realisasi -->
                        <div class="mb-3">
                          <label class="form-label">Usulan Realisasi</label>
                          <input type="text" class="form-control" id="usulan_realisasi" name="usulan_realisasi" readonly>
                        </div>

                        <!-- Bukti Dukung -->
                        <div class="mb-3">
                          <label class="form-label">Bukti Dukung</label>
                          <p id="bukti_dukung_text"><a href="#" target="_blank" id="bukti_dukung_link">Lihat Bukti Dukung</a></p>
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-3">
                          <label class="form-label">Keterangan</label>
                          <textarea class="form-control" id="keterangan_approve" name="keterangan" readonly rows="3"></textarea>
                        </div>

                        <!-- Status Persetujuan -->
                        <div class="mb-3">
                          <label for="status_persetujuan" class="form-label">Status Persetujuan</label>
                          <select class="form-select" id="status_persetujuan" name="status_persetujuan" required>
                            <option value="diterima">diterima</option>
                            <option value="ditolak">ditolak</option>
                          </select>
                        </div>

                        <!-- Pesan Persetujuan -->
                        <div class="mb-3">
                          <label for="pesan_persetujuan" class="form-label">Pesan</label>
                          <textarea class="form-control" id="pesan_persetujuan" name="pesan_persetujuan" rows="3" placeholder="Masukkan pesan"></textarea>
                        </div>

                        <!-- Footer Modal -->
                        <div class="modal-footer border-top-0">
                          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                          <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                      </form>
                    </div>
                  </div>
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

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="{{ asset('assets/js/detail-monitoring-kegiatan.js') }}"></script>

  <!-- Vendor JS Files -->
  <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
  <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/quill/quill.js') }}"></script>
  <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
  <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>

  <!-- Template Main JS File -->
  <script src="{{ asset('assets/js/main.js') }}"></script>

  <script>
    document.querySelectorAll('.satuan-kerja').forEach(td => {
      td.addEventListener('click', function() {
        const satuanKerjaId = this.getAttribute('data-id');
        const monitoringKegiatanId = '{{ $monitoringKegiatan->id }}';

        if (!satuanKerjaId) return;

        const modalBody = document.getElementById('riwayatTableBody');
        const recordInfo = document.getElementById('recordInfo');
        const prevBtn = document.getElementById('previousPage');
        const nextBtn = document.getElementById('nextPage');
        const pageNumbers = document.getElementById('pageNumbers');
        const recordsPerPageSelect = document.getElementById('recordsPerPage');
        const monthSelect = document.getElementById('monthSelect');
        const exportBtn = document.getElementById('export-riwayat');

        // Array nama bulan dalam bahasa Indonesia
        const bulanIndo = [
          'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        // Fungsi konversi bulan Inggris + tahun ke format Indonesia
        function formatBulanIndo(bulanInggrisTahun) {
          const m = moment(bulanInggrisTahun, 'MMMM YYYY');
          const bulan = m.month(); // 0-11
          const tahun = m.year();
          return `${bulanIndo[bulan]} ${tahun}`;
        }

        modalBody.innerHTML = '<tr><td colspan="4" class="text-center">Memuat data...</td></tr>';

        fetch(`/riwayat-update-realisasi/${satuanKerjaId}/${monitoringKegiatanId}`)
          .then(res => res.json())
          .then(data => {
            // Isi dropdown bulan dengan nama bulan Bahasa Indonesia tapi value tetap bahasa Inggris
            monthSelect.innerHTML = '';
            data.bulanOptions.forEach(bulan => {
              const option = document.createElement('option');
              option.value = bulan; // format bahasa Inggris (contoh: January 2025)
              option.textContent = formatBulanIndo(bulan); // tampilkan dalam bahasa Indonesia
              monthSelect.appendChild(option);
            });

            // Default ke bulan berjalan (format bahasa Inggris)
            const currentMonth = moment().format('MMMM YYYY');
            const defaultIndex = Array.from(monthSelect.options).findIndex(opt => opt.value === currentMonth);
            monthSelect.selectedIndex = defaultIndex >= 0 ? defaultIndex : 0;

            // Fungsi filter data per bulan tetap pakai format Inggris untuk filter
            const filterByMonth = (bulan, updates) => updates.filter(item => {
              const date = moment(item.tanggal, 'DD-MM-YYYY');
              return date.format('MMMM YYYY') === bulan;
            });

            let filtered = filterByMonth(monthSelect.value, data.updates);
            let currentPage = 1;
            let itemsPerPage = parseInt(recordsPerPageSelect.value);
            let totalPages = Math.ceil(filtered.length / itemsPerPage);

            function renderTable() {
              modalBody.innerHTML = '';
              if (filtered.length === 0) {
                modalBody.innerHTML = '<tr><td colspan="4" class="text-center">Data kosong untuk bulan ini.</td></tr>';
                recordInfo.textContent = '';
                pageNumbers.innerHTML = '';
                prevBtn.disabled = true;
                nextBtn.disabled = true;
                return;
              }
              const start = (currentPage - 1) * itemsPerPage;
              const pageData = filtered.slice(start, start + itemsPerPage);

              pageData.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>${row.tanggal}</td>
                <td>${row.realisasi_harian}</td>
                <td>${row.akumulasi}</td>
                <td>${row.persentase}</td>`;
                modalBody.appendChild(tr);
              });

              recordInfo.textContent = `Menampilkan ${start + 1} - ${start + pageData.length} dari ${filtered.length} data`;

              // Pagination Buttons
              pageNumbers.innerHTML = '';
              for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = 'btn btn-sm btn-light';
                if (i === currentPage) btn.classList.add('btn-primary');
                btn.onclick = () => {
                  currentPage = i;
                  renderTable();
                };
                pageNumbers.appendChild(btn);
              }

              prevBtn.disabled = currentPage === 1;
              nextBtn.disabled = currentPage === totalPages;
            }

            prevBtn.onclick = () => {
              if (currentPage > 1) {
                currentPage--;
                renderTable();
              }
            };
            nextBtn.onclick = () => {
              if (currentPage < totalPages) {
                currentPage++;
                renderTable();
              }
            };
            recordsPerPageSelect.onchange = () => {
              itemsPerPage = parseInt(recordsPerPageSelect.value);
              totalPages = Math.ceil(filtered.length / itemsPerPage);
              currentPage = 1;
              renderTable();
            };

            function renderChart() {
              const labels = filtered.map(u => u.tanggal);
              const dataAkumulasi = filtered.map(u => u.akumulasi);

              if (window.riwayatChartInstance) window.riwayatChartInstance.destroy();

              const ctx = document.getElementById('riwayatChart').getContext('2d');
              window.riwayatChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                  labels,
                  datasets: [{
                    label: 'Realisasi Sampel',
                    data: dataAkumulasi,
                    borderColor: 'rgba(75,192,192,1)',
                    borderWidth: 2,
                    fill: false,
                  }],
                },
                options: {
                  responsive: true,
                  plugins: {
                    legend: {
                      display: false // jika ingin legend di-hide
                    },
                    title: {
                      display: true,
                      text: `Realisasi Sampel untuk ${formatBulanIndo(monthSelect.value)}`,
                      font: {
                        size: 18,
                        weight: 'bold'
                      },
                      padding: {
                        top: 10,
                        bottom: 30
                      },
                    },
                  },
                  scales: {
                    x: {
                      title: {
                        display: true,
                        text: 'Tanggal',
                        font: {
                          weight: 'bold',
                          size: 15
                        }
                      }
                    },
                    y: {
                      title: {
                        display: true,
                        text: 'Realisasi Sampel',
                        font: {
                          weight: 'bold',
                          size: 15
                        }
                      }
                    },
                  },
                },
              });
            }

            // Render awal
            renderTable();
            renderChart();

            monthSelect.onchange = () => {
              filtered = filterByMonth(monthSelect.value, data.updates);
              currentPage = 1;
              totalPages = Math.ceil(filtered.length / itemsPerPage);
              renderTable();
              renderChart();
            };

            exportBtn.href = `/export-riwayat-realisasi/${monitoringKegiatanId}/${satuanKerjaId}`;

            new bootstrap.Modal(document.getElementById('infoSatuanKerjaModal')).show();
          })
          .catch(err => {
            modalBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>';
            console.error(err);
          });
      });
    });

    // Fungsi untuk memperbarui grafik berdasarkan bulan yang dipilih
    function updateChartForMonth(selectedMonth, updates) {
      const selectedMonthStart = moment(selectedMonth, 'MMMM YYYY').startOf('month');
      const selectedMonthEnd = moment(selectedMonth, 'MMMM YYYY').endOf('month');

      const filteredData = updates.filter(item => {
        const itemDate = moment(item.tanggal, 'DD-MM-YYYY');
        return itemDate.isBetween(selectedMonthStart, selectedMonthEnd, null, '[]');
      });

      const labelsForMonth = filteredData.map(item => item.tanggal);
      const dataForMonth = filteredData.map(item => item.akumulasi);

      // Hancurkan chart lama jika ada
      if (window.riwayatChartInstance) {
        window.riwayatChartInstance.destroy();
      }

      const ctx = document.getElementById('riwayatChart').getContext('2d');
      window.riwayatChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labelsForMonth,
          datasets: [{
            label: 'Realisasi Sampel',
            data: dataForMonth,
            borderColor: 'rgba(75,192,192,1)',
            borderWidth: 2,
            fill: false
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: `Realisasi Sampel untuk ${selectedMonth}`,
              font: {
                size: 18,
                weight: 'bold'
              },
              padding: {
                top: 10,
                bottom: 30
              }
            }
          },
          scales: {
            x: {
              title: {
                display: true,
                text: 'Tanggal',
                font: {
                  weight: 'bold',
                  size: 15
                }
              }
            },
            y: {
              title: {
                display: true,
                text: 'Realisasi Sampel',
                font: {
                  weight: 'bold',
                  size: 15
                }
              }
            }
          }
        }
      });
    }

    document.addEventListener('click', function(e) {
      if (e.target.closest('.approve-btn')) {
        const btn = e.target.closest('.approve-btn');

        const idUpdateRealisasi = btn.getAttribute('data-id');
        const idTargetRealisasi = btn.getAttribute('data-id-target-realisasi');
        const usulanRealisasi = btn.getAttribute('data-realisasi');
        const buktiDukung = btn.getAttribute('data-bukti');
        const keterangan = btn.getAttribute('data-keterangan') || '';

        document.getElementById('id_update_realisasi').value = idUpdateRealisasi;
        document.getElementById('id_target_realisasi_approve').value = idTargetRealisasi;
        document.getElementById('usulan_realisasi').value = usulanRealisasi;
        document.getElementById('bukti_dukung_link').href = buktiDukung;
        document.getElementById('keterangan_approve').value = keterangan;

        document.getElementById('pesan_persetujuan').value = '';
        document.getElementById('status_persetujuan').value = 'diterima';
      }
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const pesanButtons = document.querySelectorAll('.pesan-info-btn');
      const pesanModal = new bootstrap.Modal(document.getElementById('pesanInfoModal'));
      const pesanContent = document.getElementById('pesanInfoContent');

      pesanButtons.forEach(button => {
        button.addEventListener('click', function() {
          const pesan = this.getAttribute('data-pesan') || '-';
          pesanContent.textContent = pesan;
          pesanModal.show();
        });
      });
    });
  </script>

  <script>
    document.addEventListener('click', function(e) {
      if (e.target.closest('.btn-edit-realisasi')) {
        const btn = e.target.closest('.btn-edit-realisasi');
        const idTargetRealisasi = btn.getAttribute('data-id');
        const realisasiSaatIni = parseFloat(btn.getAttribute('data-realisasi')) || 0;
        const target = parseFloat(btn.getAttribute('data-target')) || 0;

        const realisasiInput = document.getElementById('realisasi');
        realisasiInput.value = realisasiSaatIni; // Tampilkan realisasi saat ini
        realisasiInput.min = realisasiSaatIni + 1;
        realisasiInput.max = target;
        realisasiInput.placeholder = `Masukkan nilai > ${realisasiSaatIni} dan ≤ ${target}`;

        document.getElementById('id_target_realisasi').value = idTargetRealisasi;
      }
    });
  </script>

  <script>
    window.routes = {
      pendingVerifikasi: "{{ route('notifications.pending-verifikasi') }}"
    };
  </script>

  <script src="{{ asset('assets/js/notification.js') }}"></script>

</html>