<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurahsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('surahs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('bismillah_pre');
            $table->string('simple_name');
            $table->string('complex_name');
            $table->string('english_name');
            $table->string('arabic_name');
            $table->string('revelation_place');
            $table->integer('revelation_order');
            $table->integer('count_verses');
            $table->integer('pages');
            $table->integer('start_page');
            $table->integer('end_page');
            $table->boolean('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('surahs');
    }
}
