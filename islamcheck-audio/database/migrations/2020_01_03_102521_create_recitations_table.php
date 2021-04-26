<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recitations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('file_name');
            $table->string('extension');
            $table->string('format_long_name');
            $table->integer('size');
            $table->integer('stream_count');
            $table->float('duration');
            $table->integer('bit_rate');
            $table->integer('probe_score');
            $table->float('start_time');
            $table->integer('qari_id')->unsigned()->nullable()->index();
            $table->integer('surah_id')->unsigned()->nullable()->index();
            $table->integer('download_count');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recitations');
    }
}
