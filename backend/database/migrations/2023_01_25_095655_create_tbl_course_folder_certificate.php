<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblCourseFolderCertificate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_course_folder_certificate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('folder_id')->nullable();
            $table->integer('certificate_id')->nullable();
            $table->integer('expiry')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_course_folder_certificate');
    }
}
