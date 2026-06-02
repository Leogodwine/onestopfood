<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('checkout_batch_id')->nullable()->after('delivery_location_id')->index();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('checkout_batch_id')->nullable()->after('order_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('checkout_batch_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('checkout_batch_id');
        });
    }
};
