<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('foreign_id');
            $table->string('key', 50);
            $table->tinyInteger('tab_id');
            $table->text('value');
            $table->text('label');
            $table->tinyInteger('type');
            $table->tinyInteger('order');
            $table->tinyInteger('is_visible');
            $table->string('style', 500);
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
        Schema::dropIfExists('options');
    }
}
