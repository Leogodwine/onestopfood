<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['chef_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('chef_id')->nullable()->change();
            $table->foreign('chef_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['chef_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('chef_id')->nullable(false)->change();
            $table->foreign('chef_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
