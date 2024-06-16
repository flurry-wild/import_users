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

        if ($uploadId === 0) {
            $uploadId = $uploadId + 1;
            $service->createNewUpload($uploadId, $this->job->getJobId());
        }

        $status = $service->getProp($uploadId, 'status');

        if ($status === 'finished') {
            $service->createNewUpload($uploadId+1, $this->job->getJobId());
        }

        $lastProcessedRow = (int)$service->getProp($uploadId, 'last_processed_row');

        $totalRows = $service->getAndSetTotalRows($uploadId);

        if ($lastProcessedRow + 1 >= $totalRows) {
            $service->setProp($uploadId, 'status', 'finished');
        } else {
            $import = new UsersImport();
            $import->startRow = $lastProcessedRow + 1;

            Excel::import($import, storage_path('app/public/import.xlsx'));

            $service->setProp($uploadId, 'last_processed_row', $lastProcessedRow + 1000);

            $jobIds = $service->getProp($uploadId, 'jobs');
            $service->setProp($uploadId, 'jobs', implode(',', array_merge(explode(',', $jobIds), [$this->job->getJobId()])));


            broadcast(new ParseUsersReport($service->info()));

            static::dispatch();
        }
    }


}
