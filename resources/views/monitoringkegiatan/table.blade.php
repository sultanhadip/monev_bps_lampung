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