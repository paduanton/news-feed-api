<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('feed_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id');
            $table->string('content');
            $table->enum('type', ['category', 'author', 'source', 'keyword', 'date']);
            $table->timestampsTz(0);
            $table->softDeletesTz('deleted_at', 0);
            $table->foreign('users_id')->references('id')->on('users');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('feed_preferences');
    }
};
