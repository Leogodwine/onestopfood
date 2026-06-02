<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->renameColumn('address_type', 'kitchen_type');
            $table->renameColumn('proof_of_address_path', 'proof_of_kitchen_path');
        });
    }

    public function down(): void
    {
        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->renameColumn('kitchen_type', 'address_type');
            $table->renameColumn('proof_of_kitchen_path', 'proof_of_address_path');
        });
    }
};
