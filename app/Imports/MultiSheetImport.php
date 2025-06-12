<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetImport implements WithMultipleSheets
{
    protected $monitoringKegiatanImport;

    public function __construct()
    {
        $this->monitoringKegiatanImport = new MonitoringKegiatanImport();
    }

    public function sheets(): array
    {
        return [
            'Input' => $this->monitoringKegiatanImport,
            // Sheet "Pedoman Pengisian" tidak dimasukkan, jadi diabaikan
        ];
    }

    /**
     * Getter untuk jumlah baris berhasil import.
     */
    public function getSuccessCount(): ?int
    {
        if (method_exists($this->monitoringKegiatanImport, 'getSuccessCount')) {
            return $this->monitoringKegiatanImport->getSuccessCount();
        }

        return null;
    }

    /**
     * Getter untuk array baris duplikat.
     */
    public function getDuplicateRows(): array
    {
        if (method_exists($this->monitoringKegiatanImport, 'getDuplicateRows')) {
            return $this->monitoringKegiatanImport->getDuplicateRows();
        }

        return [];
    }
}
