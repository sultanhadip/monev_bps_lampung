<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetImportKelolaUser implements WithMultipleSheets
{
    protected $kelolaUserImport;

    public function __construct()
    {
        $this->kelolaUserImport = new KelolaUserImport();
    }

    public function sheets(): array
    {
        // Gunakan instance yang sudah dibuat agar data duplikat dan sukses tersimpan
        return [
            'Input' => $this->kelolaUserImport,
            // Abaikan sheet lain
        ];
    }

    public function getSuccessCount(): ?int
    {
        if (method_exists($this->kelolaUserImport, 'getSuccessCount')) {
            return $this->kelolaUserImport->getSuccessCount();
        }

        return null;
    }

    public function getDuplicateRows(): array
    {
        if (method_exists($this->kelolaUserImport, 'getDuplicateRows')) {
            return $this->kelolaUserImport->getDuplicateRows();
        }

        return [];
    }
}
