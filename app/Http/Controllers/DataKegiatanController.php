<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\DataKegiatanImport;
use App\Imports\MultiSheetImport;
use App\Imports\MultiSheetImportDataKegiatan;
use App\Models\DataKegiatan;
use App\Models\TimKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DataKegiatanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter pencarian, records per page, dan filter tim kerja
        $search = $request->input('search');
        $recordsPerPage = $request->input('recordsPerPage', 10);
        $filterTim = $request->input('filter_tim');

        // Ambil semua tim kerja untuk dropdown
        $timkerja = TimKerja::all();

        // Query utama data kegiatan
        $query = DataKegiatan::query();

        // Filter pencarian jika ada
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', '%' . $search . '%')
                    ->orWhere('objek_kegiatan', 'like', '%' . $search . '%')
                    ->orWhere('periode_kegiatan', 'like', '%' . $search . '%');
                // biasanya filter kode_tim tidak perlu, karena itu id, kecuali kamu simpan string tim di kolom kode_tim
            });
        }

        // Filter berdasarkan tim kerja jika filter_tim ada
        if ($filterTim) {
            $query->where('id_tim_kerja', $filterTim);
        }

        // Filter tambahan untuk Admin Provinsi jika perlu
        if (Auth::check() && Auth::user()->hasRole('isAdminProv')) {
            $timKerjaUser = Auth::user()->timkerja;
            if ($timKerjaUser) {
                $query->where('id_tim_kerja', $timKerjaUser->id);
            }
        }

        // Pagination dengan appends agar query string tidak hilang saat pindah halaman
        $datakegiatan = $query->paginate($recordsPerPage)->appends([
            'search' => $search,
            'recordsPerPage' => $recordsPerPage,
            'filter_tim' => $filterTim,
        ]);

        $totalRecords = $datakegiatan->total();

        $showingText = 'Showing ' . $datakegiatan->firstItem() . '-' . $datakegiatan->lastItem() . ' of ' . $totalRecords . ' records';

        // Jika permintaan Ajax, kembalikan partial view dan text showing
        if ($request->ajax()) {
            return response()->json([
                'table' => view('datakegiatan.table', compact('datakegiatan', 'timkerja'))->render(),
                'showingText' => $showingText,
            ]);
        }

        // Render view lengkap, sertakan filterTim supaya dropdown bisa menampilkan pilihan yang terpilih
        return view('data-kegiatan', compact('datakegiatan', 'timkerja', 'totalRecords', 'showingText', 'filterTim'));
    }

    public function store(Request $request)
    {
        // Validasi request termasuk kode_tim harus ada di tabel tim_kerjas
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255|unique:data_kegiatans,nama_kegiatan',
            'kode_tim' => 'required|exists:tim_kerjas,id',  // Harus sesuai dengan ID yang benar
            'objek_kegiatan' => 'required|string|max:255',
            'periode_kegiatan' => 'required|string|max:255',
        ]);

        // Cari tim kerja berdasarkan ID yang dipilih dari form
        $timKerja = TimKerja::find($request->kode_tim);

        if (!$timKerja) {
            return back()->withErrors(['kode_tim' => 'Kode tim tidak ditemukan.']);
        }

        // Simpan data dengan id_tim_kerja yang sesuai
        DataKegiatan::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'kode_tim' => $timKerja->id,  // Pastikan menyimpan kode tim
            'objek_kegiatan' => $request->objek_kegiatan,
            'periode_kegiatan' => $request->periode_kegiatan,
            'id_tim_kerja' => $timKerja->id  // Pastikan menyimpan ID tim kerja
        ]);

        return redirect()->route('data-kegiatan')->with('success', 'Data kegiatan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255|unique:data_kegiatans,nama_kegiatan,' . $id,
            'kode_tim' => 'required|exists:tim_kerjas,id',  // Pastikan kode_tim adalah ID yang valid
            'objek_kegiatan' => 'required|string|max:255',
            'periode_kegiatan' => 'required|string|max:255',
        ]);

        $datakegiatan = DataKegiatan::findOrFail($id);
        $timKerja = TimKerja::find($request->kode_tim);

        if (!$timKerja) {
            return back()->withErrors(['kode_tim' => 'Kode tim tidak ditemukan.']);
        }

        // Perbarui data kegiatan dengan id_tim_kerja baru
        $datakegiatan->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'kode_tim' => $timKerja->id,
            'objek_kegiatan' => $request->objek_kegiatan,
            'periode_kegiatan' => $request->periode_kegiatan,
            'id_tim_kerja' => $timKerja->id
        ]);

        return redirect()->route('data-kegiatan')->with('success', 'Data kegiatan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $datakegiatan = DataKegiatan::findOrFail($id);
        $datakegiatan->delete();

        return redirect()->route('data-kegiatan')->with('success', 'Data kegiatan berhasil dihapus');
    }

    public function import(Request $request)
    {
        // Validasi file
        $request->validate([
            'excel_file' => 'required|mimes:xls,xlsx|max:10240', // Limit file size to 10MB
        ], [
            'excel_file.required' => 'File Excel harus diunggah.',
            'excel_file.mimes' => 'Hanya file Excel dengan format xls atau xlsx yang diperbolehkan.',
            'excel_file.max' => 'Ukuran file terlalu besar. Maksimal 10MB.',
        ]);

        try {
            // Buat instance import agar bisa akses jumlah sukses dan duplikat
            $import = new DataKegiatanImport();

            // Proses import
            Excel::import($import, $request->file('excel_file'));

            // Ambil jumlah sukses, duplikat, dan error jika ada
            $successCount = $import->getSuccessCount() ?? 0;
            $duplicateRows = $import->getDuplicateRows() ?? [];
            $errorRows = $import->getErrorRows() ?? [];

            // Siapkan redirect untuk halaman hasil import
            $redirect = redirect()->route('data-kegiatan');

            // Jika semua baris adalah duplikat
            if (count($duplicateRows) > 0 && $successCount == 0) {
                // Tampilkan hanya pesan duplikat jika tidak ada yang berhasil disimpan
                $lines = [];
                foreach ($duplicateRows as $row) {
                    $lines[] = "- Nama Kegiatan: \"{$row['nama_kegiatan']}\", Tim: {$row['nama_tim']}";
                }
                $duplicateMsg = "Semua data adalah duplikat:\n" . implode("\n", $lines);
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
                    $lines[] = "- Nama Kegiatan: \"{$row['nama_kegiatan']}\", Tim: {$row['nama_tim']}";
                }
                $duplicateMsg = "Daftar kegiatan yang sudah ditambahkan:\n" . implode("\n", $lines);
                $redirect = $redirect->with('duplicate_errors', $duplicateMsg);
            }

            // Jika ada error rows (kolom yang hilang atau data kosong)
            if (!empty($errorRows)) {
                $lines = [];
                foreach ($errorRows as $row) {
                    $lines[] = "- Nama Kegiatan: \"{$row['nama_kegiatan']}\", Tim: {$row['nama_tim']}";
                }
                $errorMsg = "Data berikut tidak valid atau kosong:\n" . implode("\n", $lines);
                $redirect = $redirect->with('error_rows', $errorMsg);
            }

            // Jika tidak ada error, tampilkan hanya sukses atau warning duplikat
            return $redirect;
        } catch (\Exception $e) {
            // Tangani kesalahan umum, misalnya file tidak dapat diproses atau masalah lainnya
            Log::error('Terjadi kesalahan saat mengimpor data Excel: ' . $e->getMessage());

            // Tampilkan pesan kesalahan umum
            return redirect()->route('data-kegiatan')->with('error', 'Terjadi kesalahan saat mengimpor data!');
        }
    }

    public function downloadFormat()
    {
        $filePath = base_path('app/Imports/format_data_kegiatan.xlsx'); // Ganti dengan path file yang sesuai
        return response()->download($filePath);
    }
}
