<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'admin_title')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('admin_title', 40)->nullable()->after('is_super_admin');
        });

        if (Schema::hasColumn('users', 'is_super_admin')) {
            DB::table('users')
                ->where('role', 'admin')
                ->where('is_super_admin', true)
                ->whereNull('admin_title')
                ->update(['admin_title' => 'system_administrator']);

            DB::table('users')
                ->where('role', 'admin')
                ->where(function ($query) {
                    $query->where('is_super_admin', false)->orWhereNull('is_super_admin');
                })
                ->whereNull('admin_title')
                ->update(['admin_title' => 'manager']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'admin_title')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_title');
        });
    }
};
