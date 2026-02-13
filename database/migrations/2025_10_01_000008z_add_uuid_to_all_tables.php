<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add UUID columns to all main tables (nullable first, then populate and make unique)

        // Users
        if (!Schema::hasColumn('users', 'uuid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('uuid')->after('id')->nullable();
            });
        }
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // Organizations
        Schema::table('organizations', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('organizations')->get()->each(function ($org) {
            DB::table('organizations')->where('id', $org->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('organizations', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // Memberships
        Schema::table('memberships', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('memberships')->get()->each(function ($membership) {
            DB::table('memberships')->where('id', $membership->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('memberships', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // Clients
        Schema::table('clients', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('clients')->get()->each(function ($client) {
            DB::table('clients')->where('id', $client->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('invoices')->get()->each(function ($invoice) {
            DB::table('invoices')->where('id', $invoice->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // Invoice Items
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('invoice_items')->get()->each(function ($item) {
            DB::table('invoice_items')->where('id', $item->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // Payments
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('payments')->get()->each(function ($payment) {
            DB::table('payments')->where('id', $payment->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // Recurring Invoices
        Schema::table('recurring_invoices', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('recurring_invoices')->get()->each(function ($invoice) {
            DB::table('recurring_invoices')->where('id', $invoice->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('recurring_invoices', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // Recurring Invoice Items
        Schema::table('recurring_invoice_items', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('recurring_invoice_items')->get()->each(function ($item) {
            DB::table('recurring_invoice_items')->where('id', $item->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('recurring_invoice_items', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // Invoice Templates - already has UUID from creation
        if (!DB::table('invoice_templates')->whereNull('uuid')->exists()) {
            // UUID column exists and is populated, just ensure it's unique
            Schema::table('invoice_templates', function (Blueprint $table) {
                if (!Schema::hasIndex('invoice_templates', 'invoice_templates_uuid_unique')) {
                    $table->unique('uuid');
                }
            });
        }

        // Currencies
        Schema::table('currencies', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('currencies')->get()->each(function ($currency) {
            DB::table('currencies')->where('id', $currency->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('currencies', function (Blueprint $table) {
            $table->unique('uuid');
        });

        // System Notifications
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });
        DB::table('system_notifications')->get()->each(function ($notification) {
            DB::table('system_notifications')->where('id', $notification->id)->update(['uuid' => (string) Str::uuid()]);
        });
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('recurring_invoices', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('recurring_invoice_items', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('system_notifications', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
