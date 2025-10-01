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
        // Add organization_id to recurring_invoices table
        Schema::table('recurring_invoices', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->onDelete('cascade');

            // Add indexes for better query performance
            $table->index(['organization_id', 'is_active']);
            $table->index(['organization_id', 'client_id']);
            $table->index(['organization_id', 'next_invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recurring_invoices', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'is_active']);
            $table->dropIndex(['organization_id', 'client_id']);
            $table->dropIndex(['organization_id', 'next_invoice_date']);
            $table->dropColumn('organization_id');
        });
    }
};
