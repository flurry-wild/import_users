<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcelFileUploadsTable extends Migration
{
    public function up()
    {
        Schema::create('excel_file_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('process'); // Поле для статуса
            $table->integer('last_processed_row')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('excel_file_uploads');
    }
}
