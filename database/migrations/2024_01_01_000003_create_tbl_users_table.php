<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_users')) {
            return;
        }
        Schema::create('tbl_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('fullname')->nullable();
            $table->string('email')->nullable();
            $table->text('password')->nullable();
            $table->string('profile_photo')->nullable();
            $table->text('bio')->nullable();
            $table->tinyInteger('is_verify')->default(0);
            $table->tinyInteger('is_freez')->default(0);
            $table->tinyInteger('is_moderator')->default(0);
            $table->tinyInteger('is_dummy')->default(0);
            $table->string('device')->nullable();
            $table->string('device_token')->nullable();
            $table->string('app_language')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_users');
    }
};
