<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'overdue_invoice', 'payment_received', 'invoice_sent', etc.
            $table->string('level')->default('info'); // 'info', 'warning', 'error', 'success'
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data for the notification

            // Recipients - can be user-specific or system-wide
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            // Related records for context
            $table->morphs('notifiable'); // Can relate to Invoice, Client, Payment, etc.

            // Status
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissed')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // For temporary notifications

            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['type', 'level']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
