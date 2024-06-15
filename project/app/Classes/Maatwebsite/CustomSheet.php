<?php

namespace App\Classes\Maatwebsite;

use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CustomSheet extends Sheet
{
    protected $customStartRow;

    public static function make(Spreadsheet $spreadsheet, $index)
    {
        if (is_numeric($index)) {
            return static::byIndex($spreadsheet, $index);
        }

        return static::byName($spreadsheet, $index);
    }

    public function setStartRow($startRow)
    {
        $this->customStartRow = $startRow;
    }

    public function getStartRow($sheetImport): int
    {
        return $this->customStartRow;
    }
}
