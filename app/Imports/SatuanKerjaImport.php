<?php

namespace App\Imports;

use App\Models\SatuanKerja;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class SatuanKerjaImport implements ToModel, WithHeadingRow
{
    protected $duplicateRows = []; // Menyimpan baris duplikat
    protected $successCount = 0; // Menyimpan jumlah data yang berhasil disimpan
    protected $errorRows = []; // Menyimpan baris yang error
    protected $expectedColumns = ['kode_satuan_kerja', 'nama_satuan_kerja']; // Kolom yang diharapkan

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Validasi struktur kolom
            $columns = array_keys($row);
            $missingColumns = array_diff($this->expectedColumns, $columns);

            if (!empty($missingColumns)) {
                // Jika ada kolom yang hilang
                $this->errorRows[] = $row;
                Log::error('Kolom yang diimpor tidak sesuai', ['missing_columns' => $missingColumns, 'row' => $row]);
                return null; // Skip jika kolom tidak sesuai
            }

            // Validasi data wajib
            if (empty($row['kode_satuan_kerja']) || empty($row['nama_satuan_kerja'])) {
                // Log error jika kolom tidak lengkap
                $this->errorRows[] = $row;
                Log::error('Data kode atau nama satuan kerja kosong', ['row' => $row]);
                return null; // Skip jika data tidak lengkap
            }

            // Cek duplikat berdasarkan kode_satuan_kerja
            $exists = SatuanKerja::where('kode_satuan_kerja', $row['kode_satuan_kerja'])->exists();
            if ($exists) {
                // Simpan data duplikat untuk ditampilkan di controller
                $this->duplicateRows[] = [
                    'kode_satuan_kerja' => $row['kode_satuan_kerja'],
                    'nama_satuan_kerja' => $row['nama_satuan_kerja'],
                ];
                return null; // Skip insert duplikat
            }

            // Simpan data satuan kerja baru
            $satuanKerja = SatuanKerja::create([
                'kode_satuan_kerja' => $row['kode_satuan_kerja'],
                'nama_satuan_kerja' => $row['nama_satuan_kerja'],
            ]);

            $this->successCount++; // Increment success count

            Log::info('Data Satuan Kerja berhasil disimpan', ['satuan_kerja' => $satuanKerja]);

            return $satuanKerja;
        } catch (\Throwable $th) {
            Log::error('Terjadi kesalahan saat mengimpor data Satuan Kerja: ' . $th->getMessage(), ['row' => $row]);
            $this->errorRows[] = $row; // Simpan row error
            return null;
        }
    }

    // Getter untuk mendapatkan baris duplikat
    public function getDuplicateRows()
    {
        return $this->duplicateRows;
    }

    // Getter untuk mendapatkan jumlah data yang berhasil disimpan
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    // Getter untuk mendapatkan baris yang error
    public function getErrorRows()
    {
        return $this->errorRows;
    }
}
