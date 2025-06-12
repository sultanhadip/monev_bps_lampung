<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\TimKerjaImport;
use App\Models\TimKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TimKerjaController extends Controller
{
    // Controller
    public function index(Request $request)
    {
        // Ambil parameter pencarian dan jumlah data per halaman dari query string
        $search = $request->input('search');
        $recordsPerPage = $request->input('recordsPerPage', 10); // Default 10

        // Query untuk mendapatkan data tim kerja
        $query = TimKerja::query();

        if ($search) {
            // Jika ada pencarian, lakukan pencarian berdasarkan kode_tim dan nama_tim
            $query->where('nama_tim', 'like', '%' . $search . '%');
        }

        // Ambil data dengan pagination
        $timkerja = $query->paginate($recordsPerPage);

        // Update teks showing (Showing 1-10 of 100 records)
        $showingText = 'Showing ' . $timkerja->firstItem() . '-' . $timkerja->lastItem() . ' of ' . $timkerja->total() . ' records';

        // Jika permintaan adalah Ajax, kembalikan hanya bagian tabel dan showingText
        if ($request->ajax()) {
            return response()->json([
                'table' => view('timkerja.table', compact('timkerja'))->render(),
                'showingText' => $showingText,
            ]);
        }

        // Jika bukan permintaan Ajax, kembalikan tampilan lengkap
        return view('tim-kerja', compact('timkerja', 'showingText'));
    }


    public function store(Request $request)
    {
        // Validasi data yang dikirimkan
        $request->validate([
            'nama_tim' => 'required|string|max:255|unique:tim_kerjas,nama_tim',
        ]);

        // Menyimpan data ke database
        $timkerja = new TimKerja();
        $timkerja->nama_tim = $request->input('nama_tim');
        $timkerja->save();

        return redirect()->route('tim-kerja')->with('success', 'Tim Kerja berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_tim' => 'required|string|max:255|unique:tim_kerjas,nama_tim,' . $id,
        ]);

        $timkerja = TimKerja::findOrFail($id);
        $timkerja->update($request->only(['nama_tim']));

        return redirect()->route('tim-kerja')->with('success', 'Tim Kerja berhasil diperbarui');
    }

    public function destroy($id)
    {
        $timkerja = TimKerja::findOrFail($id);
        $timkerja->delete();

        return redirect()->route('tim-kerja')->with('success', 'Tim Kerja berhasil dihapus');
    }

    public function import(Request $request)
    {
        // Validasi file
        $request->validate([
            'excel_file' => 'required|mimes:xls,xlsx'
        ]);

        try {
            // Buat instance import agar bisa akses jumlah sukses dan duplikat
            $import = new TimKerjaImport();

            // Proses import
            Excel::import($import, $request->file('excel_file'));

            // Ambil jumlah sukses dan duplikat jika ada
            $successCount = method_exists($import, 'getSuccessCount') ? $import->getSuccessCount() : 0;
            $duplicateRows = method_exists($import, 'getDuplicateRows') ? $import->getDuplicateRows() : [];
            $errorRows = method_exists($import, 'getErrorRows') ? $import->getErrorRows() : [];

            // Pesan sukses hanya dikirim jika ada data yang berhasil disimpan
            if ($successCount > 0) {
                $successMsg = "{$successCount} data berhasil disimpan.";
                $redirect = redirect()->route('tim-kerja')->with('success', $successMsg);
            } else {
                $redirect = redirect()->route('tim-kerja');
            }

            // Jika ada duplikat, tampilkan pesan duplikat
            if (!empty($duplicateRows)) {
                $lines = [];
                foreach ($duplicateRows as $row) {
                    $lines[] = "- {$row['nama_tim']}";
                }

                $duplicateMsg = "Daftar Tim kerja berikut sudah ditambahkan:\n" . implode("\n", $lines);
                $redirect = $redirect->with('duplicate_errors', $duplicateMsg);
            }

            // Jika ada error rows (kolom yang hilang atau data kosong)
            if (!empty($errorRows)) {
                $errorMsg = "Data berikut tidak valid atau kolom kosong:\n" . implode("\n", $errorRows);
                return $redirect->with('error', $errorMsg);
            }

            // Gabungkan pesan sukses dan duplikat jika ada
            return $redirect;
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Menangani kesalahan jika file tidak valid
            Log::error('Kesalahan saat memvalidasi file Excel: ' . $e->getMessage());
            return redirect()->route('tim-kerja')->with('error', 'Terjadi kesalahan saat memvalidasi file Excel. Kolom tidak sesuai.');
        } catch (\Exception $e) {
            // Tangani kesalahan umum (misalnya jika ada masalah dengan file)
            Log::error('Terjadi kesalahan saat mengimpor data Excel: ' . $e->getMessage());
            return redirect()->route('tim-kerja')->with('error', 'Terjadi kesalahan saat mengimpor data!');
        }
    }

    public function downloadFormat()
    {
        $filePath = base_path('app/Imports/format_tim_kerja.xlsx'); // Ganti dengan path file yang sesuai
        return response()->download($filePath);
    }
}
