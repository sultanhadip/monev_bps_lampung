<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sertifikat;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

class SertifikatController extends Controller
{
    public function index(Request $request)
    {
        // Menentukan jumlah records per halaman, default 10
        $recordsPerPage = $request->input('recordsPerPage', 10);  // Ambil nilai dari query string atau default ke 10

        // Ambil data sertifikat sesuai dengan jumlah yang diinginkan
        $sertifikat = Sertifikat::paginate($recordsPerPage);

        // Kirim data ke view
        return view('sertifikat', compact('sertifikat'));
    }


    public function generateCertificate($sertifikatId, $download = false)
    {
        // Mengambil data sertifikat berdasarkan ID
        $sertifikat = Sertifikat::find($sertifikatId);

        if (!$sertifikat) {
            // Jika sertifikat tidak ditemukan, beri response atau throw error
            return response()->json(['error' => 'Sertifikat tidak ditemukan'], 404);
        }

        // Ambil data dari objek sertifikat
        $nomor_sertifikat = $sertifikat->nomor_sertifikat;
        $nama_satuan_kerja = $sertifikat->nama_satuan_kerja;
        $peringkat = $sertifikat->peringkat;
        $periode_kinerja = $sertifikat->periode_kinerja;

        // Membuat instance PDF
        $pdf = new Fpdi();

        // Menambahkan halaman kosong berwarna putih dengan ukuran A4
        $pdf->AddPage('L', 'A4');  // 'P' untuk Portrait, 'A4' untuk ukuran A4

        // Menambahkan logo pertama di pojok kiri atas
        $pdf->Image('assets/img/bps.png', 10, 10, 45); // Ganti dengan path logo Anda

        // Menambahkan logo kedua di pojok kanan atas
        $pdf->Image('assets/img/logoberakhlak.png', 197, 10, 45); // Ganti dengan path logo Anda

        // Menambahkan logo ketiga di pojok kanan atas
        $pdf->Image('assets/img/logomelayani.png', 245, 12, 30); // Path ke file logo, X, Y, ukuran (45mm lebar)

        $pdf->SetFont('Arial', 'BI', 12);
        $pdf->SetXY(47, 18); // Posisi X disesuaikan agar tulisan rapat dengan logo
        $pdf->Cell(0, 5, 'BADAN PUSAT STATISTIK', 0, 1, 'L');

        $pdf->SetFont('Arial', 'BI', 12);
        $pdf->SetX(47); // Mengatur posisi X tetap rapat dengan logo
        $pdf->Cell(0, 7, 'PROVINSI LAMPUNG', 0, 1, 'L');

        $pdf->Ln(10); // Jarak antar elemen setelah header

        // Konten Tengah
        $pdf->Ln(8); // Memberikan jarak dari header
        $pdf->SetFont('Arial', 'B', 30);
        $pdf->Cell(0, 12, 'SERTIFIKAT', 0, 1, 'C');
        $pdf->Ln(1.5); // Jarak antar elemen
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->MultiCell(0, 12, "Nomor: $nomor_sertifikat", 0, 'C');
        $pdf->Ln(1.5); // Jarak antar elemen sekitar 1.5
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 6, 'Diberikan Kepada', 0, 1, 'C');
        $pdf->Ln(1.5); // Jarak antar elemen sekitar 1.5
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $nama_satuan_kerja, 0, 1, 'C');
        $pdf->Ln(1.5); // Jarak untuk paragraf berikutnya
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 7, "Sebagai Satuan Kerja dengan Kinerja Terbaik $peringkat pada periode $periode_kinerja", 0, 'C'); // Mengambil keterangan dari database
        $pdf->Ln(1.5); // Jarak untuk paragraf berikutnya
        $pdf->MultiCell(0, 7, "di Lingkungan Badan Pusat Statistik Provinsi Lampung", 0, 'C');
        $pdf->Ln(1.5); // Jarak untuk paragraf berikutnya
        $pdf->Cell(0, 30, 'Bandar Lampung, ' . date('d F Y'), 0, 1, 'C');
        $pdf->Ln(15);

        // Footer
        $pdf->SetY(-80); // Posisi naik sedikit
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 6, 'Kepala Badan Pusat Statistik', 0, 1, 'C');
        $pdf->Cell(0, 6, 'Provinsi Lampung', 0, 1, 'C');
        $pdf->Ln(25);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 6, 'Atas Parlindungan Lubis', 0, 1, 'C');

        // Menambahkan ornamen besar yang menutupi seluruh pojok sertifikat
        $pdf->Image('assets/img/ornamen.png', 0, 0, 297, 210); // Menutupi seluruh halaman A4, landscape mode

        // Menentukan apakah kita akan mendownload atau menampilkan PDF
        if ($download) {
            // Jika $download true, maka PDF diunduh
            return $pdf->Output('D', 'sertifikat_' . $nomor_sertifikat . '.pdf');
        } else {
            // Jika $download false, maka PDF ditampilkan
            return $pdf->Output('I', 'Sertifikat_' . $nomor_sertifikat . '.pdf');
        }
    }

    public function generate()
    {
        // Mendapatkan bulan dan tahun saat ini
        $currentPeriod = now()->format('m-Y');  // Format bulan-tahun (misal: 01-2025)

        // Menghapus sertifikat lama yang sudah ada untuk periode ini
        Sertifikat::whereHas('penilaian', function ($query) use ($currentPeriod) {
            $query->where('periode_kinerja', $currentPeriod);
        })->delete();

        // Mengambil data peringkat 1, 2, dan 3 dari penilaian
        $topThreePenilaian = Penilaian::where('periode_kinerja', $currentPeriod)
            ->whereIn('peringkat', [1, 2, 3])
            ->orderBy('peringkat', 'asc') // Urutkan berdasarkan peringkat
            ->get();

        // Generate sertifikat untuk peringkat 1, 2, dan 3
        foreach ($topThreePenilaian as $penilaian) {
            // Membuat nomor sertifikat unik
            $nomorSertifikat = 'SERT-' . strtoupper(Str::random(6));

            // Menyimpan nomor sertifikat untuk satuan kerja dengan peringkat terbaik
            Sertifikat::create([
                'id_penilaian' => $penilaian->id,  // Menyimpan ID penilaian
                'nomor_sertifikat' => $nomorSertifikat,  // Menyimpan nomor sertifikat
            ]);
        }

        return response()->json(['message' => 'Sertifikat untuk 3 peringkat terbaik berhasil digenerate.']);
    }
}
