<?php

namespace App\Http\Controllers;

use App\Imports\KelolaUserImport;
use App\Imports\MultiSheetImportKelolaUser;
use App\Models\User;
use App\Models\SatuanKerja;
use App\Models\TimKerja;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class KelolaUserController extends Controller
{
    // Menampilkan halaman dengan data user dan dropdown untuk role, satuan kerja, dan tim kerja
    public function index(Request $request)
    {
        $search = $request->input('search');
        $satuanKerjaId = $request->input('satuan_kerja');
        $timKerjaId = $request->input('tim_kerja');
        $recordsPerPage = $request->input('recordsPerPage', 10);
        $page = $request->input('page', 1);  // ambil page, default 1

        $query = User::query();

        $currentUser = Auth::user();
        $isAdminSatker = $currentUser && $currentUser->role === 'Admin Satuan Kerja';

        if ($isAdminSatker) {
            // Hanya user di satuan kerja admin satker yang login
            $userSatuanKerjaId = $currentUser->kode_satuan_kerja;
            $query->where('kode_satuan_kerja', $userSatuanKerjaId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        if ($satuanKerjaId) {
            $query->where('kode_satuan_kerja', $satuanKerjaId);
        }

        if ($timKerjaId) {
            $query->where('kode_tim', $timKerjaId);
        }

        // Paginate dengan page yang diambil dari request
        $kelolauser = $query->paginate($recordsPerPage, ['*'], 'page', $page);

        $showingText = 'Showing ' . $kelolauser->firstItem() . '-' . $kelolauser->lastItem() . ' of ' . $kelolauser->total() . ' records';

        $roles = Role::all();
        // PENTING: Batasi satuan kerja pada dropdown jika admin satker
        if ($isAdminSatker) {
            $satuanKerja = SatuanKerja::where('id', $currentUser->kode_satuan_kerja)->get();
        } else {
            $satuanKerja = SatuanKerja::all();
        }
        $timKerja = TimKerja::all();

        if ($request->ajax()) {
            $paginationHtml = view('kelolauser.pagination', compact('kelolauser'))->render();

            return response()->json([
                'table' => view('kelolauser.table', compact('kelolauser', 'satuanKerja', 'timKerja'))->render(),
                'showingText' => $showingText,
                'paginationHtml' => $paginationHtml,
                'satuanKerja' => $satuanKerja,
                'timKerja' => $timKerja,
            ]);
        }

        return view('kelola-user', compact('kelolauser', 'showingText', 'roles', 'satuanKerja', 'timKerja'));
    }

    // Menyimpan data user baru
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $isAdminSatker = $currentUser->role === 'Admin Satuan Kerja';

        // Override input jika Admin Satker
        if ($isAdminSatker) {
            $request->merge([
                'role' => 'Operator',
                'kode_satuan_kerja' => $currentUser->kode_satuan_kerja,
            ]);
        }

        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'role' => 'required|string',
            'kode_satuan_kerja' => 'required|exists:satuan_kerjas,id',
            'kode_tim' => 'required|exists:tim_kerjas,id',
        ]);

        $kelolauser = new User();
        $kelolauser->nama = $validatedData['nama'];
        $kelolauser->username = $validatedData['username'];
        $kelolauser->password = bcrypt($validatedData['password']);
        $kelolauser->role = $validatedData['role'];
        $kelolauser->kode_satuan_kerja = $validatedData['kode_satuan_kerja'];
        $kelolauser->kode_tim = $validatedData['kode_tim'];
        $kelolauser->save();

        if ($request->ajax()) {
            return response()->json([
                'message' => 'User berhasil ditambahkan',
                'data' => $kelolauser,
            ]);
        }

        return redirect()->route('kelola-user')->with('success', 'User berhasil ditambahkan');
    }

    // Memperbarui data user
    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        $isAdminSatker = $currentUser->role === 'Admin Satuan Kerja';

        if ($isAdminSatker) {
            $request->merge([
                'role' => 'Operator',
                'kode_satuan_kerja' => $currentUser->kode_satuan_kerja,
            ]);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string',
            'kode_satuan_kerja' => 'required|exists:satuan_kerjas,id',
            'kode_tim' => 'required|exists:tim_kerjas,id',
        ]);

        $kelolauser = User::findOrFail($id);
        $kelolauser->nama = $request->input('nama');
        $kelolauser->username = $request->input('username');

        if ($request->filled('password')) {
            $kelolauser->password = bcrypt($request->input('password'));
        }

        $kelolauser->role = $request->input('role');
        $kelolauser->kode_satuan_kerja = $request->input('kode_satuan_kerja');
        $kelolauser->kode_tim = $request->input('kode_tim');
        $kelolauser->save();

        return redirect()->route('kelola-user')->with('success', 'User berhasil diperbarui');
    }

    // Menghapus user
    public function destroy($id)
    {
        $kelolauser = User::findOrFail($id);
        $kelolauser->delete();

        return redirect()->route('kelola-user')->with('success', 'User berhasil dihapus');
    }

    public function downloadFormat()
    {
        $filePath = base_path('app/Imports/format_user.xlsx'); // Ganti dengan path file yang sesuai
        return response()->download($filePath);
    }

    public function import(Request $request)
    {
        // Validasi file
        $request->validate([
            'excel_file' => 'required|mimes:xls,xlsx'
        ]);

        try {
            // Buat instance import agar bisa akses jumlah sukses dan duplikat
            $import = new KelolaUserImport();

            // Proses import
            Excel::import($import, $request->file('excel_file'));

            // Ambil jumlah sukses dan duplikat
            $successCount = $import->getSuccessCount() ?? 0;
            $duplicateRows = $import->getDuplicateRows() ?? [];
            $errorRows = $import->getErrorRows() ?? [];

            // Siapkan redirect untuk halaman hasil import
            $redirect = redirect()->route('kelola-user');

            // Jika ada error rows (kolom yang hilang atau data kosong)
            if (!empty($errorRows)) {
                $lines = [];
                foreach ($errorRows as $row) {
                    $lines[] = "- Nama: \"{$row['nama']}\", Username: {$row['username']}";
                }
                $errorMsg = "Data berikut tidak valid atau kolomnya hilang:\n" . implode("\n", $lines);
                return $redirect->with('error', $errorMsg);
            }

            // Jika semua baris adalah duplikat
            if (count($duplicateRows) > 0 && $successCount == 0) {
                // Tampilkan hanya pesan duplikat jika tidak ada yang berhasil disimpan
                $lines = [];
                foreach ($duplicateRows as $row) {
                    $lines[] = "- Nama: \"{$row['nama']}\", Username: {$row['username']}";
                }
                $duplicateMsg = "Daftar username yang sudah digunakan:\n" . implode("\n", $lines);
                return $redirect->with('duplicate_errors', $duplicateMsg);
            }

            // Jika ada baris yang berhasil disimpan, tampilkan jumlah baris yang berhasil disimpan
            if ($successCount > 0) {
                $successMsg = "{$successCount} data berhasil disimpan.";
                $redirect = $redirect->with('success', $successMsg);
            }

            // Jika ada duplikat, tampilkan pesan duplikat
            if (!empty($duplicateRows)) {
                $lines = [];
                foreach ($duplicateRows as $row) {
                    $lines[] = "- Nama: \"{$row['nama']}\", Username: {$row['username']}";
                }
                $duplicateMsg = "Daftar username yang sudah digunakan:\n" . implode("\n", $lines);
                $redirect = $redirect->with('duplicate_errors', $duplicateMsg);
            }

            return $redirect;
        } catch (\Exception $e) {
            // Tangani kesalahan umum dan log errornya
            Log::error('Terjadi kesalahan saat mengimpor data Excel: ' . $e->getMessage());
            return redirect()->route('kelola-user')->with('error', 'Terjadi kesalahan saat mengimpor data!');
        }
    }
}
