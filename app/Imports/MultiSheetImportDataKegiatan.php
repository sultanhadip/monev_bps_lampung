<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetImportDataKegiatan implements WithMultipleSheets
{
    protected $dataKegiatanImport;

    public function __construct()
    {
        $this->dataKegiatanImport = new DataKegiatanImport();
    }

    public function sheets(): array
    {
        return [
            'Input' => $this->dataKegiatanImport,
        ];
    }

    public function getSuccessCount(): ?int
    {
        return method_exists($this->dataKegiatanImport, 'getSuccessCount')
            ? $this->dataKegiatanImport->getSuccessCount()
            : null;
    }

    public function getDuplicateRows(): array
    {
        return method_exists($this->dataKegiatanImport, 'getDuplicateRows')
            ? $this->dataKegiatanImport->getDuplicateRows()
            : [];
    }
}
