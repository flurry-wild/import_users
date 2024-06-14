<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcelFileUploadsJobsLinkTable extends Migration
{
    public function up()
    {
        Schema::create('excel_file_uploads_jobs_link', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('excel_file_upload_id');
            $table->foreign('excel_file_upload_id')->references('id')->on('excel_file_uploads')->onDelete('cascade');
            $table->unsignedBigInteger('job_id');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('excel_file_uploads_jobs_link');
    }
}
