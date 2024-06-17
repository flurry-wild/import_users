<?php

namespace App\Imports;

use App\Models\Row;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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

            $validator = Validator::make($row, [
                'id' => 'required|int|unique:rows,external_id',
                'name' => 'required|string|regex:/^[a-zA-Z ]+$/',
                'date' => 'required|date|date_format:d.m.Y',
            ]);

            if ($validator->fails()) {
                $errors[$row['id']] = $validator->errors()->all();
                foreach ($errors as $key => $errorMessages) {
                    Log::channel('import_rows')->error($key . ' - ' . implode(', ', $errorMessages) . PHP_EOL);
                }

                return null;
            }

            return new Row([
                'external_id' => $row['id'],
                'name' => $row['name'],
                'date' => Carbon::createFromFormat('d.m.Y', $row['date'])->format('Y-m-d')
            ]);
        } else {
            return null; // Возвращаем null, если счетчик превышен
        }
    }
}
