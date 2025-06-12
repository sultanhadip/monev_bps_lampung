<?php

namespace App\Http\Controllers;

use App\Models\target_realisasi_satker;
use App\Models\update_target_realisasi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RealisasiExport;
use Log;
use Carbon\Carbon; // Tambahkan Carbon

class UpdateRealisasiController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'id_target_realisasi' => 'required',
            'realisasi_satker' => 'required|numeric',
            'bukti_dukung_realisasi' => 'required|file|mimes:pdf|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        $targetRealisasi = target_realisasi_satker::find($validated['id_target_realisasi']);

        if (!$targetRealisasi) {
            return back()->with('error', 'Target realisasi tidak ditemukan.');
        }

        $realisasiSaatIni = $targetRealisasi->realisasi_satker ?? 0;
        $target = $targetRealisasi->target_satker;

        // Validasi agar realisasi baru harus > realisasi saat ini dan ≤ target
        if ($validated['realisasi_satker'] <= $realisasiSaatIni) {
            return back()->with('error', 'Nilai realisasi harus lebih besar dari realisasi saat ini.');
        }

        if ($validated['realisasi_satker'] > $target) {
            return back()->with('error', 'Nilai realisasi tidak boleh melebihi target satuan kerja.');
        }

        $existingProposal = update_target_realisasi::where('id_target_realisasi', $validated['id_target_realisasi'])
            ->where('status', 'menunggu verifikasi')
            ->first();

        try {
            $filePath = $request->file('bukti_dukung_realisasi')->store('bukti-dukung', 'public');

            if ($existingProposal) {
                $existingProposal->update([
                    'realisasi_satker' => $validated['realisasi_satker'],
                    'bukti_dukung_realisasi' => $filePath,
                    'keterangan' => $validated['keterangan'] ?? '-',
                    'status' => 'Menunggu Verifikasi',
                    'updated_at' => Carbon::now(),
                ]);
                return back()->with('success', 'Usulan Realisasi berhasil diperbarui!');
            } else {
                update_target_realisasi::create([
                    'id_target_realisasi' => $validated['id_target_realisasi'],
                    'realisasi_satker' => $validated['realisasi_satker'],
                    'bukti_dukung_realisasi' => $filePath,
                    'keterangan' => $validated['keterangan'] ?? '-',
                    'status' => 'Menunggu Verifikasi',
                    'created_at' => Carbon::now(),
                ]);
                return back()->with('success', 'Berhasil mengajukan update realisasi');
            }
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan realisasi: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            'id_update_realisasi' => 'required|exists:update_target_realisasis,id',
            'id_target_realisasi_approve' => 'required|exists:target_realisasi_satkers,id',
            'status_persetujuan' => 'required|in:diterima,ditolak',
            'pesan_persetujuan' => 'nullable|string',
        ]);

        try {
            // Ambil proposal terbaru yang statusnya 'menunggu verifikasi'
            $proposalToApprove = update_target_realisasi::where('id_target_realisasi', $validated['id_target_realisasi_approve'])
                ->where('status', 'menunggu verifikasi')
                ->latest('created_at') // Ambil yang terbaru
                ->first();

            if ($proposalToApprove) {
                // Update status proposal yang disetujui
                $proposalToApprove->update([
                    'status' => $validated['status_persetujuan'],
                    'pesan' => $validated['pesan_persetujuan'] ?? '',
                ]);

                // Hapus proposal lain yang statusnya masih menunggu verifikasi
                update_target_realisasi::where('id_target_realisasi', $validated['id_target_realisasi_approve'])
                    ->where('id', '!=', $proposalToApprove->id)
                    ->where('status', 'menunggu verifikasi')
                    ->delete(); // Hapus proposal lainnya

                return redirect()->back()->with('success', 'Berhasil verifikasi usulan realisasi');
            }

            return redirect()->back()->with('error', 'Gagal verifikasi usulan realisasi');
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat menyetujui usulan', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Gagal verifikasi usulan realisasi' . $e->getMessage());
        }
    }

    public function export($id_kegiatan)
    {
        return Excel::download(new RealisasiExport($id_kegiatan), 'realisasi_kegiatan.xlsx');
    }
}
