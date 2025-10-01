<?php

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run if there's no organization yet
        if (Organization::count() === 0) {
            // Create default organization
            $defaultOrg = Organization::create([
                'name' => config('app.name', 'Invoice System'),
                'slug' => Str::slug(config('app.name', 'invoice-system')),
                'email' => 'info@'.parse_url(config('app.url'), PHP_URL_HOST),
                'is_active' => true,
                'settings' => [
                    'currency' => 'USD',
                    'timezone' => config('app.timezone', 'UTC'),
                    'date_format' => 'Y-m-d',
                    'invoice_prefix' => 'INV',
                ],
            ]);

            // Assign all existing users to default organization as owners
            User::all()->each(function ($user) use ($defaultOrg) {
                Membership::create([
                    'user_id' => $user->id,
                    'organization_id' => $defaultOrg->id,
                    'role' => MembershipRole::Owner->value,
                    'is_active' => true,
                    'invitation_accepted_at' => now(),
                ]);
            });

            // Migrate all existing data to the default organization
            DB::table('clients')
                ->whereNull('organization_id')
                ->update(['organization_id' => $defaultOrg->id]);

            DB::table('invoices')
                ->whereNull('organization_id')
                ->update(['organization_id' => $defaultOrg->id]);

            DB::table('payments')
                ->whereNull('organization_id')
                ->update(['organization_id' => $defaultOrg->id]);

            DB::table('recurring_invoices')
                ->whereNull('organization_id')
                ->update(['organization_id' => $defaultOrg->id]);

            if (Schema::hasTable('invoice_templates')) {
                DB::table('invoice_templates')
                    ->whereNull('organization_id')
                    ->update(['organization_id' => $defaultOrg->id]);
            }

            DB::table('system_notifications')
                ->whereNull('organization_id')
                ->update(['organization_id' => $defaultOrg->id]);
        }

        // Now make organization_id NOT NULL
        Schema::table('clients', function ($table) {
            $table->foreignId('organization_id')->nullable(false)->change();
        });

        Schema::table('invoices', function ($table) {
            $table->foreignId('organization_id')->nullable(false)->change();
        });

        Schema::table('payments', function ($table) {
            $table->foreignId('organization_id')->nullable(false)->change();
        });

        Schema::table('recurring_invoices', function ($table) {
            $table->foreignId('organization_id')->nullable(false)->change();
        });

        if (Schema::hasTable('invoice_templates')) {
            Schema::table('invoice_templates', function ($table) {
                $table->foreignId('organization_id')->nullable(false)->change();
            });
        }

        Schema::table('system_notifications', function ($table) {
            $table->foreignId('organization_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make organization_id nullable again
        Schema::table('clients', function ($table) {
            $table->foreignId('organization_id')->nullable()->change();
        });

        Schema::table('invoices', function ($table) {
            $table->foreignId('organization_id')->nullable()->change();
        });

        Schema::table('payments', function ($table) {
            $table->foreignId('organization_id')->nullable()->change();
        });

        Schema::table('recurring_invoices', function ($table) {
            $table->foreignId('organization_id')->nullable()->change();
        });

        if (Schema::hasTable('invoice_templates')) {
            Schema::table('invoice_templates', function ($table) {
                $table->foreignId('organization_id')->nullable()->change();
            });
        }

        Schema::table('system_notifications', function ($table) {
            $table->foreignId('organization_id')->nullable()->change();
        });
    }
};
