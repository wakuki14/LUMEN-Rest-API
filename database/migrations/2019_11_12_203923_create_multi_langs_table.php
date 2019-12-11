<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultiLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multi_langs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model', 50);
            $table->bigInteger('foreign_id');
            $table->tinyInteger('locale');
            $table->string('field', 50);
            $table->text('content');
            $table->tinyInteger('source');
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
        Schema::dropIfExists('multi_langs');
    }
}
