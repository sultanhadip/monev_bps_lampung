@foreach($datakegiatan as $key => $tim)
<tr>
    <td scope="row">{{ $key + 1 }}</td>
    <td>{{ $tim->kode_kegiatan }}</td>
    <td>{{ $tim->nama_kegiatan }}</td>
    <td>{{ $tim->timkerja->nama_tim ?? 'Tidak ada tim' }}</td> <!-- Menampilkan nama_tim -->
    <td>{{ $tim->objek_kegiatan }}</td>
    <td>{{ $tim->periode_kegiatan }}</td>
    <td>
        <!-- Edit Button -->
        <button
            class="btn btn-sm btn-icon btn-white text-primary border-0"
            data-bs-toggle="modal"
            data-bs-target="#editModal{{ $tim->id }}">
            <i class="bi bi-pencil-square fs-6"></i>
        </button>

        <!-- Delete Button -->
        <form action="{{ route('datakegiatan.destroy', $tim->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="btn btn-sm btn-icon btn-white text-danger border-0">
                <i class="bi bi-trash fs-6"></i>
            </button>
        </form>
    </td>
</tr>

<!-- Edit Modal -->
<div class="modal fade" id="editModal{{ $tim->id }}" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('datakegiatan.update', $tim->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_kegiatan" class="form-label">Kode Kegiatan</label>
                        <input type="text" class="form-control" name="kode_kegiatan" value="{{ $tim->kode_kegiatan }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                        <input type="text" class="form-control" name="nama_kegiatan" value="{{ $tim->nama_kegiatan }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="kode_tim" class="form-label">Tim Kerja</label>
                        <select class="form-select" id="kode_tim" name="kode_tim" required>
                            <option value="">Pilih Tim Kerja</option>
                            @foreach ($timkerja as $t)
                            <option value="{{ $t->id }}" {{ $tim->id_tim_kerja == $t->id ? 'selected' : '' }}>
                                {{ $t->nama_tim }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="objek_kegiatan" class="form-label">Objek Kegiatan</label>
                        <select class="form-select" name="objek_kegiatan" required>
                            <option value="">Pilih Objek Kegiatan</option>
                            <option value="Rumah Tangga" {{ $tim->objek_kegiatan == 'Rumah Tangga' ? 'selected' : '' }}>Rumah Tangga</option>
                            <option value="Usaha" {{ $tim->objek_kegiatan == 'Usaha' ? 'selected' : '' }}>Usaha</option>
                            <option value="Lainnya" {{ $tim->objek_kegiatan == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="periode_kegiatan" class="form-label">Periode Kegiatan</label>
                        <select class="form-select" name="periode_kegiatan" required>
                            <option value="">Pilih Periode Kegiatan</option>
                            <option value="Bulanan" {{ $tim->periode_kegiatan == 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
                            <option value="Triwulan" {{ $tim->periode_kegiatan == 'Triwulan' ? 'selected' : '' }}>Triwulan</option>
                            <option value="Semesteran" {{ $tim->periode_kegiatan == 'Semesteran' ? 'selected' : '' }}>Semesteran</option>
                            <option value="Tahunan" {{ $tim->periode_kegiatan == 'Tahunan' ? 'selected' : '' }}>Tahunan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn text-white" style="background-color: rgb(250, 82, 82);" data-bs-dismiss="modal" aria-label="Close">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary text-white">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Edit Modal -->

@endforeach