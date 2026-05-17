<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('checkout_request_id')->nullable()->after('provider_reference');
            $table->string('merchant_request_id')->nullable()->after('checkout_request_id');
            $table->string('mpesa_receipt')->nullable()->after('merchant_request_id');
            $table->string('failure_reason')->nullable()->after('mpesa_receipt');
            $table->timestamp('paid_at')->nullable()->after('failure_reason');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'checkout_request_id',
                'merchant_request_id',
                'mpesa_receipt',
                'failure_reason',
                'paid_at',
            ]);
        });
    }
};
