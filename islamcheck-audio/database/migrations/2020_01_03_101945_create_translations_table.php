<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('original_name');
            $table->string('description')->nullable();
            $table->integer('source_id')->unsigned()->nullable()->index();
            $table->char('language_code',4)->nullable()->index();
            $table->string('source_type');

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
        Schema::dropIfExists('translations');
    }
}
