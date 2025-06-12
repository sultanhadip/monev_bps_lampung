<?php

namespace App\Imports;

use App\Models\User;
use App\Models\SatuanKerja;
use App\Models\TimKerja;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Tambahkan untuk memproses header otomatis

class KelolaUserImport implements ToModel, WithHeadingRow
{
    protected $duplicateRows = [];
    protected $successCount = 0;
    protected $errorRows = []; // Menyimpan baris yang error

    public function model(array $row)
    {
        try {
            // Validasi kolom yang dibutuhkan
            if (!isset($row['nama'], $row['username'], $row['password'], $row['role'], $row['kode_satuan_kerja'], $row['nama_tim'])) {
                Log::error('Data yang dibutuhkan tidak lengkap', ['row' => $row]);
                $this->errorRows[] = $row; // Menyimpan baris yang error
                return null; // Skip baris yang tidak lengkap
            }

            // Cek satuan kerja
            $satker = SatuanKerja::where('kode_satuan_kerja', $row['kode_satuan_kerja'])->first();
            if (!$satker) {
                Log::error('Satuan kerja tidak ditemukan: ' . $row['kode_satuan_kerja'], ['row' => $row]);
                $this->errorRows[] = $row; // Menyimpan baris yang error
                return null;
            }

            // Cek tim kerja
            $tim = TimKerja::where('nama_tim', $row['nama_tim'])->first();
            if (!$tim) {
                Log::error('Tim tidak ditemukan: ' . $row['nama_tim'], ['row' => $row]);
                $this->errorRows[] = $row; // Menyimpan baris yang error
                return null;
            }

            // Cek duplikat berdasarkan username
            $exists = User::where('username', $row['username'])->exists();
            if ($exists) {
                $this->duplicateRows[] = [
                    'nama' => $row['nama'],
                    'username' => $row['username'],
                ];
                return null; // Skip insert duplikat
            }

            // Simpan user baru
            $kelolaUser = User::create([
                'kode_satuan_kerja' => $satker->id,
                'kode_tim' => $tim->id,
                'nama' => $row['nama'],
                'username' => $row['username'],
                'password' => bcrypt($row['password']),
                'role' => $row['role'],
            ]);

            $this->successCount++; // Increment success count

            Log::info('Data User berhasil disimpan', ['kelola_user' => $kelolaUser]);

            return $kelolaUser;
        } catch (\Throwable $th) {
            Log::error('Terjadi kesalahan saat mengimpor data User: ' . $th->getMessage(), ['row' => $row]);
            $this->errorRows[] = $row; // Menyimpan baris yang error
            return null;
        }
    }

    // Getter untuk duplikat untuk controller
    public function getDuplicateRows()
    {
        return $this->duplicateRows;
    }

    // Getter untuk jumlah sukses untuk controller
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
