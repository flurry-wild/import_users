<?php

namespace App\Jobs;

use App\Imports\UsersImport;
use App\Services\UsersImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ParseUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected UsersImportService $service;

    public function setDeps(UsersImportService $service)
    {
        $this->service = $service;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $uploadId = $this->service->getUploadId();

        if (empty($uploadId)) {
            $this->service->createNewUpload(1, $this->job->getJobId());
        }

        $uploadId = $this->service->getUploadId();

        $status = $this->service->getProp($uploadId, 'status');

        if ($status === 'finished') {
            $this->service->createNewUpload($uploadId+1, $this->job->getJobId());
        }

        $lastProcessedRow = (int)$this->service->getProp($uploadId, 'last_processed_row');

        $totalRows = Excel::toArray(new UsersImport, storage_path('app/public/import.xlsx'));
        $totalRowsCount = count($totalRows[0]);

        if ($lastProcessedRow + 1 >= $totalRowsCount) {
            $this->service->setProp($uploadId, 'status', 'finished');
        } else {
            $import = new UsersImport();
            $import->startRow = $lastProcessedRow + 1;

            Excel::import($import, storage_path('app/public/import.xlsx'));

            $this->service->setProp($uploadId, 'last_processed_row', $lastProcessedRow + 1000);

            $jobIds = $this->service->getProp($uploadId, 'jobs');
            $this->service->setProp($uploadId, 'jobs', implode(',', array_merge(explode(',', $jobIds), [$this->job->getJobId()])));

            static::dispatch();
        }
    }
}
