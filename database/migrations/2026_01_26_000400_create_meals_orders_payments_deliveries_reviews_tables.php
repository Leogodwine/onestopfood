<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chef_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('prep_time_minutes')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('category')->nullable();
            $table->string('dietary_tags')->nullable(); // "vegan,halal"
            $table->string('image_path')->nullable();
            $table->boolean('is_available')->default(true)->index();
            $table->timestamps();

            $table->index(['chef_id', 'is_available']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('chef_id')->constrained('users')->cascadeOnDelete();

            $table->string('status')->default('pending')->index();
            // pending -> accepted -> preparing -> ready -> out_for_delivery -> delivered -> cancelled

            $table->text('special_instructions')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->foreignId('delivery_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['chef_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_id')->constrained()->restrictOnDelete();

            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('method')->index(); // mpesa/tigo/airtel/card/cod
            $table->string('status')->default('pending')->index(); // pending/paid/failed/refunded
            $table->decimal('amount', 10, 2);
            $table->string('provider_reference')->nullable();
            $table->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('traveler_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status')->default('unassigned')->index();
            // unassigned -> assigned -> picked_up -> delivered -> failed

            $table->decimal('traveler_earning', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('chef_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('traveler_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedTinyInteger('chef_rating')->nullable();
            $table->unsignedTinyInteger('traveler_rating')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['chef_id', 'chef_rating']);
            $table->index(['traveler_id', 'traveler_rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('meals');
    }
};

