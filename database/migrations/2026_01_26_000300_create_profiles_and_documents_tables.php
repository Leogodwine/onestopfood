<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chef_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('specialties')->nullable();
            $table->string('kitchen_address')->nullable();
            $table->string('food_handler_certificate_no')->nullable();
            $table->timestamps();
        });

        Schema::create('traveler_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('vehicle_type')->nullable(); // motorcycle, bicycle, car
            $table->string('vehicle_registration_no')->nullable();
            $table->boolean('is_online')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('user_verification_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('type'); // nida, license, food_handler_cert, insurance, etc.
            $table->string('document_no')->nullable();
            $table->string('file_path')->nullable(); // future: stored file

            $table->string('status')->default('pending')->index(); // pending/approved/rejected
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_verification_documents');
        Schema::dropIfExists('traveler_profiles');
        Schema::dropIfExists('chef_profiles');
    }
};

