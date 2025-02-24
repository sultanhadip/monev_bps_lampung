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
                      <td>{{ $monitoringKegiatan->waktu_selesai ?? '-' }}</td>
                    </tr>
                  </tbody>
                </table>

              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Realisasi Kegiatan Menurut Wilayah</h5>
              <!-- Table with hoverable rows and horizontal scroll -->
              <div style="overflow-x: auto;">
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
                      <th scope="col">Keterangan</th>
                      @endif

                      @if ($canAccessVerifikasi)
                      <th scope="col">Verifikasi</th>
                      @endif
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($targetRealisasiSatker as $index => $item)
                    <tr>
                      <td>{{ $item->satuankerja->nama_satuan_kerja ?? 'N/A' }}</td>
                      <td>{{ $item->target_satker ?? '-' }}</td>
                      <td>{{ $item->realisasi_satker ?? '-' }}</td>
                      <td>{{ $item->persentase ?? '-' }}</td>
                      <td>{{ $item->updateRealisasi->created_at ?? '-' }}</td>
                      @if ($canAccessPengajuanKeterangan)
                      <td>
                        <div class="d-flex align-items-center justify-content-center">
                          <button class="btn btn-sm btn-white text-primary border-0 btn-edit-realisasi"
                            data-bs-toggle="modal" data-bs-target="#addRealisasiModal"
                            data-id="{{ $item->id }}"
                            data-realisasi="{{ $item->realisasi_satker }}">
                            <i class="fas fa-edit fs-6"></i>
                          </button>
                        </div>
                      </td>
                      <td>{{ $preTargetRealisasiSatker[$index]->pre_keterangan ?? '-' }}</td>
                      @endif
                      @if ($canAccessVerifikasi)
                      <td>
                        <div class="d-flex align-items-center justify-content-center">
                          @if ($preTargetRealisasiSatker[$index]->updateRealisasi && $preTargetRealisasiSatker[$index]->updateRealisasi->status == 'Menunggu Verifikasi')
                          <button
                            class="btn btn-sm btn-white text-primary approve-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#approveProposalModal"
                            data-id="{{ $preTargetRealisasiSatker[$index]->updateRealisasi->id }}"
                            data-realisasi="{{ $preTargetRealisasiSatker[$index]->pre_realisasi_satker }}"
                            data-bukti="{{ asset('storage/' . $preTargetRealisasiSatker[$index]->pre_bukti_dukung) }}">
                            <i class="bi bi-shield-check fs-6"></i> Verifikasi
                          </button>
                          @else
                          {{ $item->updateRealisasi->status ?? 'Belum ada usulan' }}
                          @endif
                        </div>
                      </td>
                      @endif
                    </tr>
                    @endforeach
                  </tbody>
                </table>

              </div>
              <!-- End Table with hoverable rows and horizontal scroll -->

              <!-- Modal Form Update Realisasi -->
              <div class="modal fade" id="addRealisasiModal" tabindex="-1" aria-labelledby="addRealisasiModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
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
                          <input type="file" class="form-control" id="bukti_dukung" name="bukti_dukung_realisasi" required>
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

              <!-- Modal Approve Realisasi -->
              <div class="modal fade" id="approveProposalModal" tabindex="-1" aria-labelledby="approveProposalModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="approveProposalModalLabel">Persetujuan Usulan Realisasi</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('approve-usulan') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_target_realisasi" value="{{ $item->id}}">

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

                        <!-- Status Persetujuan -->
                        <div class="mb-3">
                          <label for="status_persetujuan" class="form-label">Status Persetujuan</label>
                          <select class="form-select" id="status_persetujuan" name="status_persetujuan" required>
                            <option value="diterima">diterima</option>
                            <option value="ditolak">ditolak</option>
                          </select>
                        </div>

                        <!-- Keterangan Persetujuan -->
                        <div class="mb-3">
                          <label for="keterangan_persetujuan" class="form-label">Keterangan</label>
                          <textarea class="form-control" id="keterangan_persetujuan" name="keterangan_persetujuan" rows="3" placeholder="Masukkan keterangan persetujuan"></textarea>
                        </div>

                        <!-- Footer Modal -->
                        <div class="modal-footer">
                          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                          <button type="submit" class="btn btn-primary">Setujui</button>
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

  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>