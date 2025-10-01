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
        // Add organization_id to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->onDelete('cascade');

            // Add indexes for better query performance
            $table->index(['organization_id', 'status', 'due_date']);
            $table->index(['organization_id', 'client_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'status', 'due_date']);
            $table->dropIndex(['organization_id', 'client_id', 'status']);
            $table->dropColumn('organization_id');
        });
    }
};
