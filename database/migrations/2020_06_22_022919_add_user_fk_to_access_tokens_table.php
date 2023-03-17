<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserFkToAccessTokensTable extends Migration
{

    public function up()
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->foreign('user_id')->constrained('users')->references('id')->on('users');
            $table->foreign('client_id')->constrained('oauth_clients')->references('id')->on('oauth_clients');
        });
    }

    public function down()
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['client_id']);
        });
    }
}
