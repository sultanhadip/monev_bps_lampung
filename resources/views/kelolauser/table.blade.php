@php
$currentUser = Auth::user();
$isAdminSatker = $currentUser && $currentUser->role === 'Admin Satuan Kerja';
@endphp

@foreach($kelolauser as $key => $tim)
<tr>
    <td scope="row">{{ $key + 1 }}</td>
    <td>{{ $tim->nama }}</td>
    <td>{{ $tim->username }}</td>
    <td>{{ $tim->role }}</td>
    <td>{{ $tim->satuanKerja->nama_satuan_kerja ?? 'Tidak ada' }}</td>
    <td>{{ $tim->timKerja->nama_tim ?? 'Tidak ada' }}</td>

    <td>
        <div class="d-flex justify-content-center">
            <button class="btn btn-sm btn-icon btn-white text-primary border-0 me-0" data-bs-toggle="modal" data-bs-target="#editModal{{ $tim->id }}">
                <i class="bi bi-pencil-square fs-6"></i>
            </button>
            <form action="{{ route('kelolauser.destroy', $tim->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-icon btn-white text-danger border-0">
                    <i class="bi bi-trash fs-6"></i>
                </button>
            </form>
        </div>
    </td>
</tr>

<!-- Edit Modal -->
<div class="modal fade" id="editModal{{ $tim->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $tim->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-1">
            <form action="{{ route('kelolauser.update', $tim->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel{{ $tim->id }}">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Nama -->
                    <div class="mb-4">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" name="nama" value="{{ $tim->nama }}" required>
                    </div>
                    <!-- Username -->
                    <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="{{ $tim->username }}" required>
                    </div>
                    <!-- Password (optional) -->
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <!-- Role -->
                    <div class="mb-4">
                        <label for="role" class="form-label">Role</label>
                        @if($isAdminSatker)
                        <input type="text" class="form-control" value="Operator" disabled>
                        <input type="hidden" name="role" value="Operator">
                        @else
                        <select class="form-control" name="role" required>
                            <option value="Kepala BPS" {{ $tim->role == 'Kepala BPS' ? 'selected' : '' }}>Kepala BPS</option>
                            <option value="Admin" {{ $tim->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Admin Provinsi" {{ $tim->role == 'Admin Provinsi' ? 'selected' : '' }}>Admin Provinsi</option>
                            <option value="Admin Satuan Kerja" {{ $tim->role == 'Admin Satuan Kerja' ? 'selected' : '' }}>Admin Satuan Kerja</option>
                            <option value="Operator" {{ $tim->role == 'Operator' ? 'selected' : '' }}>Operator</option>
                        </select>
                        @endif
                    </div>
                    <!-- Satuan Kerja -->
                    <div class="mb-4">
                        <label for="kode_satuan_kerja" class="form-label">Satuan Kerja</label>
                        @if($isAdminSatker)
                        <input type="text" class="form-control" value="{{ $currentUser->satuanKerja->nama_satuan_kerja }}" disabled>
                        <input type="hidden" name="kode_satuan_kerja" value="{{ $currentUser->kode_satuan_kerja }}">
                        @else
                        <select class="form-control" name="kode_satuan_kerja" required>
                            @foreach($satuanKerja as $satuan)
                            <option value="{{ $satuan->id }}" {{ $tim->kode_satuan_kerja == $satuan->id ? 'selected' : '' }}>{{ $satuan->nama_satuan_kerja }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <!-- Tim Kerja -->
                    <div class="mb-4">
                        <label for="kode_tim" class="form-label">Tim Kerja</label>
                        <select class="form-control" name="kode_tim" required>
                            @foreach($timKerja as $kerja)
                            <option value="{{ $kerja->id }}" {{ $tim->kode_tim == $kerja->id ? 'selected' : '' }}>{{ $kerja->nama_tim }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach