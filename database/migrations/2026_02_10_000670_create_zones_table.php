<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('service_type')->nullable(); // e.g. delivery, pickup
            $table->decimal('base_fee', 10, 2)->default(0);
            $table->unsignedInteger('traveler_capacity')->nullable();
            $table->json('polygon')->nullable(); // store coordinates as GeoJSON-style array
            $table->json('operating_hours')->nullable(); // keyed by day-of-week
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('zone_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable(); // chef or traveler
            $table->timestamps();
            $table->unique(['zone_id', 'user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_user');
        Schema::dropIfExists('zones');
    }
};

