<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_admin')) {
            return;
        }
        Schema::create('tbl_admin', function (Blueprint $table) {
            $table->id();
            $table->string('admin_username')->unique();
            $table->text('admin_password');
            $table->string('user_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_admin');
    }
};
