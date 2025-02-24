<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TimKerja;
use Illuminate\Http\Request;

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
            $query->where('kode_tim', 'like', '%' . $search . '%')
                ->orWhere('nama_tim', 'like', '%' . $search . '%');
        }

        // Ambil data dengan pagination
        $timkerja = $query->paginate($recordsPerPage);

        // Hitung total data
        $totalRecords = $query->count();

        // Jika permintaan adalah Ajax, kembalikan hanya bagian tabel
        if ($request->ajax()) {
            return view('timkerja.table', compact('timkerja'))->render();
        }

        // Jika bukan permintaan Ajax, kembalikan tampilan lengkap
        return view('tim-kerja', compact('timkerja', 'totalRecords'));
    }

    public function store(Request $request)
    {
        // Validasi data yang dikirimkan
        $request->validate([
            'kode_tim' => 'required|string|max:255|unique:tim_kerjas,kode_tim',
            'nama_tim' => 'required|string|max:255',
        ]);

        // Menyimpan data ke database
        $timkerja = new TimKerja();
        $timkerja->kode_tim = $request->input('kode_tim');
        $timkerja->nama_tim = $request->input('nama_tim');
        $timkerja->save();

        return redirect()->route('tim-kerja')->with('success', 'Tim Kerja berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_tim' => 'required|string|max:255|unique:tim_kerjas,kode_tim,' . $id,
            'nama_tim' => 'required|string|max:255',
        ]);

        $timkerja = TimKerja::findOrFail($id);
        $timkerja->update($request->only(['kode_tim', 'nama_tim']));

        return redirect()->route('tim-kerja')->with('success', 'Tim Kerja berhasil diperbarui');
    }

    public function destroy($id)
    {
        $timkerja = TimKerja::findOrFail($id);
        $timkerja->delete();

        return redirect()->route('tim-kerja')->with('success', 'Tim Kerja berhasil dihapus');
    }
}
