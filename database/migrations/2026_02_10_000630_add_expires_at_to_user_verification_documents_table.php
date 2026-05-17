<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_verification_documents', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('user_verification_documents', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};

