<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\User;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('users', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 100);
                $table->string('email', 255)->unique();
                $table->string('phone', 20)->nullable(true);
                $table->string('password', 255)->nullable(true);
                $table->string('avatar', 255)->nullable(true);
                $table->string('access_token', 255)->nullable(true);
                $table->string('fb_token', 255)->nullable(true);
                $table->string('google_token', 255)->nullable(true);
                $table->dateTime('last_login')->nullable(true);
                $table->tinyInteger('status')->default(User::STATUS_ACTIVE);
                $table->tinyInteger('deleted')->default(User::DELETE_NO);
                $table->nullableTimestamps();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
