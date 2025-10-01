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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();

            // Core relationships
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            // Role and status
            $table->string('role')->default('user'); // owner, admin, manager, accountant, user
            $table->boolean('is_active')->default(true);

            // Invitation fields
            $table->string('invited_email')->nullable();
            $table->string('invitation_token')->nullable()->unique();
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamp('invitation_accepted_at')->nullable();
            $table->timestamp('invitation_expires_at')->nullable();
            $table->foreignId('invited_by_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'organization_id', 'is_active']);
            $table->index(['organization_id', 'role']);
            $table->index('invitation_token');
            $table->index(['invited_email', 'organization_id']);

            // Unique constraint: one active membership per user per organization
            $table->unique(['user_id', 'organization_id'], 'user_org_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
