<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SatuanKerja;
use Illuminate\Http\Request;

class SatuanKerjaController extends Controller
{

    public function index(Request $request)
    {
        // Ambil parameter pencarian dan jumlah data per halaman dari query string
        $search = $request->input('search');
        $recordsPerPage = $request->input('recordsPerPage', 10);

        // Query untuk mendapatkan data satuan kerja
        $query = SatuanKerja::query();

        if ($search) {
            // Jika ada pencarian, lakukan pencarian berdasarkan kode_satuan_kerja dan nama_satuan_kerja
            $query->where('kode_satuan_kerja', 'like', '%' . $search . '%')
                ->orWhere('nama_satuan_kerja', 'like', '%' . $search . '%');
        }

        // Ambil data dengan pagination
        $satuankerja = $query->paginate($recordsPerPage);

        // Hitung total data
        $totalRecords = $query->count();

        // Jika permintaan adalah Ajax, kembalikan hanya bagian tabel
        if ($request->ajax()) {
            return view('satuankerja.table', compact('satuankerja'))->render();
        }

        // Jika bukan permintaan Ajax, kembalikan tampilan lengkap
        return view('satuan-kerja', compact('satuankerja', 'totalRecords'));
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
}
