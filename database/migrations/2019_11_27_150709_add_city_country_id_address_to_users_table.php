<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCityCountryIdAddressToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('city_id')->nullable(true);
            $table->integer('country_id')->nullable(true);
            $table->tinyInteger('gender')->nullable(true);
            $table->date('birthday')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->drop('city_id');
            $table->drop('country_id');
            $table->drop('gender');
            $table->drop('birthday');
        });
    }
}
