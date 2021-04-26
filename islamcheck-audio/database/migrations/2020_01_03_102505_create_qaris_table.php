<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQarisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qaris', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('arabic_name')->nullable();
            $table->string('relative_path');
            $table->string('file_formats')->nullable();
            $table->mediumText('description')->nullable();
            $table->integer('section_id')->unsigned()->nullable()->index();
            $table->boolean('home');
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
        Schema::dropIfExists('qaris');
    }
}
