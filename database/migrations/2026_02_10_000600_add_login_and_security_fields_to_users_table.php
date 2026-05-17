<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Admin / security fields
            $table->boolean('is_super_admin')->default(false)->after('role');

            // Login tracking
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('approved_at');
            $table->timestamp('last_login_at')->nullable()->after('failed_login_attempts');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->text('last_login_user_agent')->nullable()->after('last_login_ip');
            $table->string('last_login_device_fingerprint')->nullable()->after('last_login_user_agent');

            // 2FA for admins
            $table->boolean('two_factor_enabled')->default(false)->after('last_login_device_fingerprint');
            $table->string('two_factor_code', 6)->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_super_admin',
                'failed_login_attempts',
                'last_login_at',
                'last_login_ip',
                'last_login_user_agent',
                'last_login_device_fingerprint',
                'two_factor_enabled',
                'two_factor_code',
                'two_factor_expires_at',
            ]);
        });
    }
};

