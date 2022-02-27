<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExperiencesPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experiences_photos', function (Blueprint $table) {
            $table->id();
            $table->integer('experiences_id');
            $table->string('place_id');
            $table->string('file_path');
            $table->string('file_path_small');
            $table->integer('width');
            $table->integer('height');
            $table->dateTime('created_at');
            $table->integer('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('experiences_photos');
    }
}
