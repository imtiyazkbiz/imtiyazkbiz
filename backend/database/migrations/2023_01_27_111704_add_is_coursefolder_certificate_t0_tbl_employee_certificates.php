<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCoursefolderCertificateT0TblEmployeeCertificates extends Migration {
    /**
     * Run the migrations.
     * @return void
     */
    public function up() {
        Schema::table('tbl_employee_certificates', function(Blueprint $table) {
            $table->integer('is_coursefolder_certificate')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down() {
        Schema::table('tbl_employee_certificates', function(Blueprint $table) {
            $table->dropColumn('is_coursefolder_certificate');
        });
    }
}
