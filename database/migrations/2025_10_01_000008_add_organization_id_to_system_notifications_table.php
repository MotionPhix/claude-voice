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
        // Add organization_id to system_notifications table
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->onDelete('cascade');

            // Add index for better query performance
            $table->index(['organization_id', 'user_id', 'is_read']);
            $table->index(['organization_id', 'type', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'user_id', 'is_read']);
            $table->dropIndex(['organization_id', 'type', 'level']);
            $table->dropColumn('organization_id');
        });
    }
};
