@foreach($timkerja as $key => $tim)
<tr>
    <td scope="row">{{ $key + 1 }}</td>
    <td>{{ $tim->kode_tim }}</td>
    <td>{{ $tim->nama_tim }}</td>
    <td>
        <!-- Edit Button -->
        <button
            class="btn btn-sm btn-icon btn-white text-primary border-0"
            data-bs-toggle="modal"
            data-bs-target="#editModal{{ $tim->id }}">
            <i class="bi bi-pencil-square fs-6"></i>
        </button>

        <!-- Delete Button -->
        <form action="{{ route('timkerja.destroy', $tim->id) }}" method="POST" style="display:inline;">
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
            <form action="{{ route('timkerja.update', $tim->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Tim Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_tim" class="form-label">Kode Tim</label>
                        <input type="text" class="form-control" name="kode_tim" value="{{ $tim->kode_tim }}">
                    </div>
                    <div class="mb-3">
                        <label for="nama_tim" class="form-label">Nama Tim</label>
                        <input type="text" class="form-control" name="nama_tim" value="{{ $tim->nama_tim }}">
                    </div>
                </div>
                <div class="modal-footer">
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