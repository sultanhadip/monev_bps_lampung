<?php

namespace App\Http\Controllers;

use App\Models\EditSertifikat;
use Illuminate\Http\Request;

class EditSertifikatController extends Controller
{
    public function edit()
    {
        $data = EditSertifikat::first();

        $nama = $data->nama ?? 'Atas Parlindungan Lubis';
        $jabatan = $data->jabatan ?? 'Kepala Badan Pusat Statistik';

        return view('edit-sertifikat', compact('nama', 'jabatan'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
        ]);

        $data = EditSertifikat::first();

        if (!$data) {
            $data = new EditSertifikat();
        }

        $data->nama = $request->nama;
        $data->jabatan = $request->jabatan;
        $data->save();

        return redirect()->route('edit-sertifikat.edit')->with('success', 'Data berhasil diperbarui');
    }
}
