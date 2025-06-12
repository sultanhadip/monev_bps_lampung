<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\SatuanKerjaImport;
use App\Models\SatuanKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SatuanKerjaController extends Controller
{

    public function index(Request $request)
    {
        // Ambil parameter pencarian dan jumlah data per halaman dari query string
        $search = $request->input('search');
        $recordsPerPage = $request->input('recordsPerPage', 10); // Default 10

        // Query untuk mendapatkan data satuan kerja
        $query = SatuanKerja::query();

        if ($search) {
            // Jika ada pencarian, lakukan pencarian berdasarkan kode_satuan_kerja dan nama_satuan_kerja
            $query->where('kode_satuan_kerja', 'like', '%' . $search . '%')
                ->orWhere('nama_satuan_kerja', 'like', '%' . $search . '%');
        }

        // Ambil data dengan pagination
        $satuankerja = $query->paginate($recordsPerPage);

        // Hitung total data yang ditemukan (hanya berdasarkan pencarian)
        $totalRecords = $satuankerja->total();

        // Buat teks untuk 'Showing ... of ...' 
        $showingText = 'Showing ' . $satuankerja->firstItem() . '-' . $satuankerja->lastItem() . ' of ' . $totalRecords . ' records';

        // Jika permintaan adalah Ajax, kembalikan hanya bagian tabel dan showingText
        if ($request->ajax()) {
            return response()->json([
                'table' => view('satuankerja.table', compact('satuankerja'))->render(),
                'showingText' => $showingText,
            ]);
        }

        // Jika bukan permintaan Ajax, kembalikan tampilan lengkap
        return view('satuan-kerja', compact('satuankerja', 'totalRecords', 'showingText'));
    }

    public function store(Request $request)
    {
        // Validasi data yang dikirimkan
        $request->validate([
            'kode_satuan_kerja' => 'required|string|max:255|unique:satuan_kerjas,kode_satuan_kerja',
            'nama_satuan_kerja' => 'required|string|max:255',
        ]);

        // Menyimpan data ke database
        $satuankerja = new SatuanKerja();
        $satuankerja->kode_satuan_kerja = $request->input('kode_satuan_kerja');
        $satuankerja->nama_satuan_kerja = $request->input('nama_satuan_kerja');
        $satuankerja->save();

        return redirect()->route('satuan-kerja')->with('success', 'Satuan Kerja berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        // Validasi dengan pengecualian pada kode_satuan_kerja yang sudah ada
        $request->validate([
            'kode_satuan_kerja' => 'required|string|max:255|unique:satuan_kerjas,kode_satuan_kerja,' . $id,
            'nama_satuan_kerja' => 'required|string|max:255',
        ]);

        // Temukan data berdasarkan ID
        $satuankerja = SatuanKerja::findOrFail($id);

        // Perbarui data yang diterima dari request
        $satuankerja->update($request->only(['kode_satuan_kerja', 'nama_satuan_kerja']));

        // Kembalikan ke halaman dengan pesan sukses
        return redirect()->route('satuan-kerja')->with('success', 'Satuan Kerja berhasil diperbarui');
    }

    public function destroy($id)
    {
        $satuankerja = SatuanKerja::findOrFail($id);
        $satuankerja->delete();

        return redirect()->route('satuan-kerja')->with('success', 'Satuan Kerja berhasil dihapus');
    }

    public function import(Request $request)
    {
        // Validasi file
        $request->validate([
            'excel_file' => 'required|mimes:xls,xlsx'
        ]);

        try {
            // Buat instance import agar bisa akses jumlah sukses, duplikat, dan error
            $import = new SatuanKerjaImport();

            // Proses import
            Excel::import($import, $request->file('excel_file'));

            // Ambil jumlah sukses, duplikat, dan error jika ada
            $successCount = method_exists($import, 'getSuccessCount') ? $import->getSuccessCount() : 0;
            $duplicateRows = method_exists($import, 'getDuplicateRows') ? $import->getDuplicateRows() : [];
            $errorRows = method_exists($import, 'getErrorRows') ? $import->getErrorRows() : [];

            // Initializing redirect
            $redirect = redirect()->route('satuan-kerja');

            // Handle duplicate rows: if all rows are duplicates, only show duplicate warning
            if (count($duplicateRows) > 0) {
                if ($successCount > 0) {
                    // If there are successful rows, show both success and duplicate warnings
                    $successMsg = "{$successCount} data berhasil disimpan.";
                    $redirect = $redirect->with('success', $successMsg);
                }

                // Showing duplicate rows in the warning
                $lines = [];
                foreach ($duplicateRows as $row) {
                    $lines[] = "- Kode Satuan Kerja: {$row['kode_satuan_kerja']}, Nama Satuan Kerja: {$row['nama_satuan_kerja']}";
                }
                $duplicateMsg = "Daftar satuan kerja berikut sudah ditambahkan:\n" . implode("\n", $lines);
                $redirect = $redirect->with('duplicate_errors', $duplicateMsg);
            }

            // Handle error rows if any
            if (!empty($errorRows)) {
                $lines = [];
                foreach ($errorRows as $row) {
                    $lines[] = "- Kode Satuan Kerja: {$row['kode_satuan_kerja']}, Nama Satuan Kerja: {$row['nama_satuan_kerja']}";
                }
                $errorMsg = "Data berikut tidak valid atau kosong:\n" . implode("\n", $lines);
                $redirect = $redirect->with('error_rows', $errorMsg);
            }

            return $redirect;
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mengimpor data Excel: ' . $e->getMessage());
            return redirect()->route('satuan-kerja')->with('error', 'Terjadi kesalahan saat mengimpor data!');
        }
    }

    public function downloadFormat()
    {
        $filePath = base_path('app/Imports/format_satuan_kerja.xlsx'); // Ganti dengan path file yang sesuai
        return response()->download($filePath);
    }
}
