<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class RiwayatRealisasiExport implements FromCollection, WithHeadings
{
    protected $riwayatRealisasi;
    protected $waktuMulai;
    protected $waktuSelesai;

    public function __construct($riwayatRealisasi, $waktuMulai, $waktuSelesai)
    {
        $this->riwayatRealisasi = $riwayatRealisasi;
        $this->waktuMulai = Carbon::parse($waktuMulai);
        $this->waktuSelesai = Carbon::parse($waktuSelesai);
    }

    public function collection()
    {
        $data = [];
        $akumulasi = 0;
        $previousRealisasi = 0; // Initialize to 0 at the start

        // Loop through all dates between waktu_mulai and waktu_selesai
        for ($date = $this->waktuMulai; $date->lte($this->waktuSelesai); $date->addDay()) {
            $formattedDate = $date->format('d-m-Y');
            $realisasiHarian = 0; // Default realisasi is 0
            $foundRealisasi = false;
            $latestUpdate = null;

            // Check if there is any realisasi on this date
            foreach ($this->riwayatRealisasi as $realisasi) {
                foreach ($realisasi->updateRealisasi as $update) {
                    $updateDate = Carbon::parse($update->created_at)->format('d-m-Y');
                    if ($formattedDate == $updateDate) {
                        // Track the latest update with the highest 'realisasi_satker' value
                        if ($latestUpdate === null || $update->realisasi_satker > $latestUpdate->realisasi_satker) {
                            $latestUpdate = $update;
                        }
                    }
                }
            }

            // If a valid update is found for this date, use the latest one
            if ($latestUpdate !== null) {
                $realisasiHarian = $latestUpdate->realisasi_satker - $previousRealisasi;
                $previousRealisasi = $latestUpdate->realisasi_satker;  // Update the previous realisasi to current
                $foundRealisasi = true;
            }

            // If no update is found for this date, set realisasi_harian to 0
            if (!$foundRealisasi) {
                $realisasiHarian = 0;
            }

            // Add the daily realisasi to the running total (akumulasi)
            $akumulasi += $realisasiHarian;

            // Calculate the percentage, ensure it's 0 if akumulasi is 0
            $persentase = ($akumulasi > 0) ? round(($akumulasi / max($realisasi->target_satker, 1)) * 100, 2) . '%' : '0.00%';

            // Ensure all fields have a value and substitute empty with 0 or appropriate default
            $data[] = [
                'Tanggal'    => $formattedDate,
                'Realisasi'  => $realisasiHarian ?: 0,  // Ensure realisasi has a value (0 if empty)
                'Akumulasi'  => $akumulasi ?: 0,        // Ensure akumulasi has a value (0 if empty)
                'Persentase' => $persentase ?: '0.00%'  // Ensure persentase has a value (0% if empty)
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Realisasi',
            'Akumulasi',
            'Persentase',
        ];
    }
}
