<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocialFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('login_from')->nullable(true);
            $table->string('facebook_id', 100)->nullable(true);
            $table->dropColumn('fb_token');
            $table->dropColumn('google_token');
            $table->string('facebook_access_token', 255)->nullable(true);
            $table->string('google_jwt_token', 2000)->nullable(true);
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
            $table->dropColumn('login_from');
            $table->dropColumn('facebook_id');
            $table->dropColumn('facebook_access_token');
            $table->dropColumn('google_jwt_token');
        });
    }
}
