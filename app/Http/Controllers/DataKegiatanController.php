<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DataKegiatan;
use App\Models\TimKerja;
use Illuminate\Http\Request;

class DataKegiatanController extends Controller
{
    public function index(Request $request)
    {
        // Get search query and records per page from query string
        $search = $request->input('search');
        $recordsPerPage = $request->input('recordsPerPage', 10);

        // Ambil data tim dari tabel tim_kerjas
        $timkerja = TimKerja::all();

        // Query for fetching data
        $query = DataKegiatan::query();

        if ($search) {
            $query->where('kode_kegiatan', 'like', '%' . $search . '%')
                ->orWhere('nama_kegiatan', 'like', '%' . $search . '%')
                ->orWhere('objek_kegiatan', 'like', '%' . $search . '%')
                ->orWhere('periode_kegiatan', 'like', '%' . $search . '%')
                ->orWhere('kode_tim', 'like', '%' . $search . '%');
        }

        // Fetch data with pagination
        $datakegiatan = $query->paginate($recordsPerPage);

        // Get the total number of records
        $totalRecords = $query->count();

        // Calculate the range of records displayed
        $from = $datakegiatan->firstItem();
        $to = $datakegiatan->lastItem();

        // If the request is AJAX, return only the table content
        if ($request->ajax()) {
            return view('datakegiatan.table', compact('datakegiatan', 'timkerja'))->render();
        }

        // If not an AJAX request, return the full view
        return view('data-kegiatan', compact('datakegiatan', 'timkerja', 'totalRecords', 'from', 'to'));
    }

    public function store(Request $request)
    {
        // Validasi request termasuk kode_tim harus ada di tabel tim_kerjas
        $request->validate([
            'kode_kegiatan' => 'required|string|max:255|unique:data_kegiatans,kode_kegiatan',
            'nama_kegiatan' => 'required|string|max:255',
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
            'kode_kegiatan' => $request->kode_kegiatan,
            'nama_kegiatan' => $request->nama_kegiatan,
            'kode_tim' => $timKerja->kode_tim,  // Pastikan menyimpan kode tim
            'objek_kegiatan' => $request->objek_kegiatan,
            'periode_kegiatan' => $request->periode_kegiatan,
            'id_tim_kerja' => $timKerja->id  // Pastikan menyimpan ID tim kerja
        ]);

        return redirect()->route('data-kegiatan')->with('success', 'Data kegiatan berhasil ditambahkan');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_kegiatan' => 'required|string|max:255|unique:data_kegiatans,kode_kegiatan,' . $id,
            'nama_kegiatan' => 'required|string|max:255',
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
            'kode_kegiatan' => $request->kode_kegiatan,
            'nama_kegiatan' => $request->nama_kegiatan,
            'kode_tim' => $timKerja->kode_tim,
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
}
