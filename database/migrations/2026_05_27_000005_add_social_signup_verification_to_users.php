<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('signup_otp_code')->nullable()->after('two_factor_expires_at');
            $table->timestamp('signup_otp_expires_at')->nullable()->after('signup_otp_code');
            $table->string('signup_otp_channel', 20)->nullable()->after('signup_otp_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_verified_at',
                'signup_otp_code',
                'signup_otp_expires_at',
                'signup_otp_channel',
            ]);
        });
    }
};
