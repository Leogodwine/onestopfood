<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_action_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 32); // deletion
            $table->text('reason');
            $table->string('status', 32)->default('pending'); // pending, approved, rejected, cancelled
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'action']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('suspended_by', 16)->nullable()->after('status');
            $table->timestamp('deactivated_at')->nullable()->after('suspended_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['suspended_by', 'deactivated_at']);
        });

        Schema::dropIfExists('account_action_requests');
    }
};
