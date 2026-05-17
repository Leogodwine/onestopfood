<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('category')->nullable(); // e.g. order_issue, payment_issue, delivery_issue
            $table->text('description');
            $table->string('status')->default('open')->index(); // open, in_review, resolved, escalated

            $table->decimal('penalty_amount', 10, 2)->nullable();
            $table->decimal('compensation_amount', 10, 2)->nullable();

            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by_admin_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};

