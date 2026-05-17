<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->text('heritage_story')->nullable()->after('description');
            $table->string('origin')->nullable()->after('heritage_story');
            $table->boolean('is_heritage')->default(false)->index()->after('is_available');
            $table->boolean('is_popular')->default(false)->index()->after('is_heritage');
        });

        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->text('heritage_story')->nullable()->after('bio');
            $table->string('years_experience')->nullable()->after('heritage_story');
            $table->string('cuisine_type')->nullable()->after('years_experience');
            $table->json('specialties_list')->nullable()->after('specialties');
        });
    }

    public function down(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->dropColumn(['heritage_story', 'origin', 'is_heritage', 'is_popular']);
        });

        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->dropColumn(['heritage_story', 'years_experience', 'cuisine_type', 'specialties_list']);
        });
    }
};
