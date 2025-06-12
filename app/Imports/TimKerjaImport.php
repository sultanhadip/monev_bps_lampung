<?php

namespace App\Imports;

use App\Models\TimKerja;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class TimKerjaImport implements ToModel, WithHeadingRow
{
    protected $duplicateRows = []; // Menyimpan baris duplikat
    protected $errorRows = []; // Menyimpan baris yang error
    protected $successCount = 0; // Menyimpan jumlah data yang berhasil disimpan
    protected $expectedColumns = ['nama_tim']; // Kolom yang diharapkan

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
            if (empty($row['nama_tim'])) {
                Log::error('Nama tim tidak boleh kosong', ['row' => $row]);
                $this->errorRows[] = $row; // Simpan row error
                return null;
            }

            // Cek duplikat berdasarkan nama_tim
            $exists = TimKerja::where('nama_tim', $row['nama_tim'])->exists();
            if ($exists) {
                // Simpan data duplikat untuk ditampilkan di controller
                $this->duplicateRows[] = [
                    'nama_tim' => $row['nama_tim'],
                ];
                return null; // Skip insert duplikat
            }

            // Simpan data tim kerja baru
            $timKerja = TimKerja::create([
                'nama_tim' => $row['nama_tim'],
            ]);

            $this->successCount++; // Increment success count

            Log::info('Data Tim Kerja berhasil disimpan', ['tim_kerja' => $timKerja]);

            return $timKerja;
        } catch (\Throwable $th) {
            Log::error('Terjadi kesalahan saat mengimpor data Tim Kerja: ' . $th->getMessage(), ['row' => $row]);
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
