<?php

namespace App\Jobs;

use App\Events\ParseUsersReport;
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

    /**
     * Execute the job.
     */
    public function handle(UsersImportService $service): void
    {
        $uploadId = $service->getUploadId();

        if (empty($uploadId)) {
            $service->createNewUpload(1, $this->job->getJobId());
        }

        $uploadId = $service->getUploadId();

        $status = $service->getProp($uploadId, 'status');

        if ($status === 'finished') {
            $service->createNewUpload($uploadId+1, $this->job->getJobId());
        }

        $lastProcessedRow = (int)$service->getProp($uploadId, 'last_processed_row');

        $totalRows = Excel::toArray(new UsersImport, storage_path('app/public/import.xlsx'));
        $totalRowsCount = count($totalRows[0]);

        if ($lastProcessedRow + 1 >= $totalRowsCount) {
            $service->setProp($uploadId, 'status', 'finished');
        } else {
            $import = new UsersImport();
            $import->startRow = $lastProcessedRow + 1;

            Excel::import($import, storage_path('app/public/import.xlsx'));

            $service->setProp($uploadId, 'last_processed_row', $lastProcessedRow + 1000);

            $jobIds = $service->getProp($uploadId, 'jobs');
            $service->setProp($uploadId, 'jobs', implode(',', array_merge(explode(',', $jobIds), [$this->job->getJobId()])));

            broadcast(new ParseUsersReport(round($lastProcessedRow/$totalRowsCount, 2) * 100));

            static::dispatch();
        }
    }
}
