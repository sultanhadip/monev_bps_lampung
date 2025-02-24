<?php

namespace App\Http\Controllers;

use App\Models\target_realisasi_satker;
use App\Models\update_target_realisasi;
use Illuminate\Http\Request;
use Log;

class UpdateRealisasiController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'id_target_realisasi' => 'required',
            'realisasi_satker' => 'required|numeric',
            'bukti_dukung_realisasi' => 'required|file|mimes:jpg,png,pdf,docx|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $filePath = $request->file('bukti_dukung_realisasi')->store('bukti-dukung', 'public');

            update_target_realisasi::create([
                'id_target_realisasi' => $validated['id_target_realisasi'],
                'realisasi_satker' => $validated['realisasi_satker'],
                'bukti_dukung_realisasi' => $filePath,
                'keterangan' => $validated['keterangan'] ?? 'Menunggu Verifikasi',
                'status' => "Menunggu Verifikasi"
            ]);

            return redirect()->back()->with('success', 'Realisasi berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error("Error updating realisasi: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate data: ' . $e->getMessage());
        }
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            'id_target_realisasi' => 'required|exists:update_target_realisasis,id',
            'status_persetujuan' => 'required|in:diterima,ditolak',
            'keterangan_persetujuan' => 'nullable|string',
        ]);

        try {
            $usulan = update_target_realisasi::findOrFail($validated['id_target_realisasi']);

            $usulan->update([
                'status' => $validated['status_persetujuan'],
                'keterangan' => $validated['keterangan_persetujuan'] ?? '',
            ]);

            Log::info('Usulan berhasil diperbarui', [
                'usulan_id' => $usulan->id,
                'status' => $validated['status_persetujuan'],
                'keterangan' => $validated['keterangan_persetujuan'] ?? ''
            ]);

            return redirect()->back()->with('success', 'Usulan berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat menyetujui usulan', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyetujui usulan: ' . $e->getMessage());
        }
    }
}
