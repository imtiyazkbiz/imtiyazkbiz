<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnsubscribesTable extends Migration {
    /**
     * Run the migrations.
     * @return void
     */
    public function up() {
        Schema::create('unsubscribes', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down() {
        Schema::dropIfExists('unsubscribes');
    }
}
