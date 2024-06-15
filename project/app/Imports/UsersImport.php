<?php

namespace App\Imports;

use App\Models\ImportedUsers;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    const LIMIT = 1000;

    public int $count = 0;
    public int $startRow;

    public function model(array $row)
    {
        if ($this->count < static::LIMIT) {
            $this->count++;

            return new ImportedUsers([
                'id' => $row['id'],
                'name' => $row['name'],
                'date' => date('Y-m-d', strtotime(str_replace('.', '-', $row['date']))),
            ]);
        } else {
            return null; // Возвращаем null, если счетчик превышен
        }
    }
}
