<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_chefs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chef_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamps();

            $table->unique(['order_id', 'chef_id']);
            $table->index(['chef_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_chefs');
    }
};
