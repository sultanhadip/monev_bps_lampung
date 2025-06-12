@foreach($satuankerja as $key => $tim)
<tr>
    <td scope="row">{{ $key + 1 }}</td>
    <td>{{ $tim->nama_satuan_kerja }}</td>
    <td>{{ $tim->kode_satuan_kerja }}</td>
    <td>
        <!-- Edit Button -->
        <button
            class="btn btn-sm btn-icon btn-white text-primary border-0"
            data-bs-toggle="modal"
            data-bs-target="#editModal{{ $tim->id }}">
            <i class="bi bi-pencil-square fs-6"></i>
        </button>

        <!-- Delete Button -->
        <form action="{{ route('satuankerja.destroy', $tim->id) }}" method="POST" style="display:inline;">
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
        <div class="modal-content rounded-1">
            <form action="{{ route('satuankerja.update', $tim->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Satuan Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_satuan_kerja" class="form-label">Kode Satuan Kerja</label>
                        <br>
                        <small class="form-text text-muted mt-0 mb-3">Masukkan kode satuan kerja yang terdiri dari 4 angka</small>
                        <input type="text" class="form-control mt-1" name="kode_satuan_kerja" value="{{ $tim->kode_satuan_kerja }}">
                    </div>
                    <div class="mb-3">
                        <label for="nama_satuan_kerja" class="form-label mb-0">Nama Satuan Kerja</label>
                        <br>
                        <small class="form-text text-muted mt-0 mb-3">Masukkan nama satuan kerja dengan huruf kapital tiap awal kata</small>
                        <input type="text" class="form-control mt-1" name="nama_satuan_kerja" value="{{ $tim->nama_satuan_kerja }}">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <!-- Button Batal -->
                    <button type="button" class="btn text-white" style="background-color: rgb(250, 82, 82);" data-bs-dismiss="modal" aria-label="Close">
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
<!-- End Edit Modal -->

@endforeach