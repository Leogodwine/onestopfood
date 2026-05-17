<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->decimal('kitchen_latitude', 10, 7)->nullable()->after('kitchen_address');
            $table->decimal('kitchen_longitude', 10, 7)->nullable()->after('kitchen_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->dropColumn(['kitchen_latitude', 'kitchen_longitude']);
        });
    }
};
