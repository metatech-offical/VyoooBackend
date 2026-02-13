<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tbl_post')) {
        Schema::create('tbl_post', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('post_type')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('video')->nullable();
            $table->integer('views')->default(0);
            $table->timestamps();
        });
        }
        if (!Schema::hasTable('report_posts')) {
        Schema::create('report_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('by_user_id')->nullable();
            $table->timestamps();
        });
        }
        if (!Schema::hasTable('report_user')) {
        Schema::create('report_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('by_user_id')->nullable();
            $table->timestamps();
        });
        }
        if (!Schema::hasTable('tbl_redeem_request')) {
        Schema::create('tbl_redeem_request', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->decimal('coins', 15, 2)->nullable();
            $table->string('gateway')->nullable();
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_redeem_request');
        Schema::dropIfExists('report_user');
        Schema::dropIfExists('report_posts');
        Schema::dropIfExists('tbl_post');
    }
};
