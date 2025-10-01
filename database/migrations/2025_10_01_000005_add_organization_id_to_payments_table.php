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
        // Add organization_id to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->onDelete('cascade');

            // Add index for better query performance
            $table->index(['organization_id', 'payment_date']);
            $table->index(['organization_id', 'invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'payment_date']);
            $table->dropIndex(['organization_id', 'invoice_id']);
            $table->dropColumn('organization_id');
        });
    }
};
