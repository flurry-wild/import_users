<?php

namespace App\Services;

use App\Imports\UsersImport;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class UsersImportService
{
    public function getUploadId(): int
    {
        return (int)Redis::get('current_file_upload_id') ?? 0;
    }

    public function createNewUpload($uploadId, $jobId): void
    {
        Redis::set('current_file_upload_id', $uploadId);

        $this->setProp($uploadId, 'status', 'process');
        $this->setProp($uploadId, 'last_processed_row', 0);
        $this->setProp($uploadId, 'total_rows', 0);

        $jobIds = $this->getProp($uploadId,'jobs') ?? '';

        $this->setProp($uploadId, 'jobs', implode(',', array_merge(explode(',', $jobIds), [$jobId])));
    }

    public function getProp($uploadId, $key): ?string
    {
        return Redis::hget('excel_file_upload_'.$uploadId, $key);
    }

    public function setProp($uploadId, $key, $value): void
    {
        Redis::hset('excel_file_upload_'.$uploadId, $key, $value);
    }

    public function getPercent($uploadId, $totalRowsCount): float
    {
        if (empty($totalRowsCount)) {
            return 0;
        }

        $lastProcessedRow = (int)$this->getProp($uploadId, 'last_processed_row');

        return round($lastProcessedRow/$totalRowsCount * 100, 2);
    }

    public function getAndSetTotalRows($uploadId): int
    {
        $totalRows = $this->getProp($uploadId, 'total_rows');

        if (empty($totalRows)) {
            $rows = Excel::toArray(new UsersImport, storage_path('app/public/import.xlsx'));
            $totalRows = count($rows[0]);
            $this->setProp($uploadId, 'total_rows', $totalRows);
        } else {
            return $totalRows;
        }

        return (int)$this->getProp($uploadId, 'total_rows');
    }

    public function info(): array
    {
        $uploadId = $this->getUploadId();

        if (empty($uploadId)) {
            return [];
        }

        return [
            'current_upload_id' => $uploadId,
            'last_processed_row' => (int)$this->getProp($uploadId, 'last_processed_row'),
            'percent_exec' => $this->getPercent($uploadId, $this->getProp($uploadId, 'total_rows')),
            'start_import_button_disabled' => $this->queueAtWork()
        ];
    }

    public function queueAtWork()
    {
        $queueLength = Redis::connection()->llen('queues:default');

        return $queueLength !== 0;
    }
}
