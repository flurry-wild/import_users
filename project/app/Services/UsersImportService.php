<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class UsersImportService
{
    public function getUploadId(): int
    {
        return (int)Redis::get('current_file_upload_id');
    }

    public function createNewUpload($uploadId, $jobId): void
    {
        Redis::set('current_file_upload_id', $uploadId);

        Redis::hset('excel_file_upload_1', 'status', 'process');
        Redis::hset('excel_file_upload_1', 'last_processed_row', 0);

        $jobIds = Redis::hget('excel_file_upload_1', 'jobs');
        Redis::hset('excel_file_upload_1', 'jobs', implode(',', array_merge(explode(',', $jobIds), [$jobId])));
    }

    public function getProp($uploadId, $key): string
    {
        return Redis::hget('excel_file_upload_'.$uploadId, $key);
    }

    public function setProp($uploadId, $key, $value): void
    {
        Redis::hset('excel_file_upload_'.$uploadId, $key, $value);
    }

    public function info()
    {
        $uploadId = $this->getUploadId();

        return [
            'current_upload_id' => $uploadId,
            'last_processed_row' => (int)$this->getProp($uploadId, 'last_processed_row'),
        ];
    }
}
