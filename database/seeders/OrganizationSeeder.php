<?php

namespace Database\Seeders;

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default organization for existing users
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

        // Attach all existing users to the default organization as owners
        User::all()->each(function ($user) use ($defaultOrg) {
            Membership::create([
                'user_id' => $user->id,
                'organization_id' => $defaultOrg->id,
                'role' => MembershipRole::Owner->value,
                'is_active' => true,
                'invitation_accepted_at' => now(),
            ]);
        });

        // Create additional demo organizations if in local environment
        if (app()->environment('local')) {
            // Create 3 demo organizations
            Organization::factory()
                ->count(3)
                ->create()
                ->each(function ($org) {
                    // Create owner membership
                    $owner = User::factory()->create();
                    Membership::factory()
                        ->owner()
                        ->create([
                            'user_id' => $owner->id,
                            'organization_id' => $org->id,
                        ]);

                    // Create additional team members
                    Membership::factory()
                        ->admin()
                        ->create([
                            'user_id' => User::factory()->create()->id,
                            'organization_id' => $org->id,
                            'invited_by_id' => $owner->id,
                        ]);

                    Membership::factory()
                        ->manager()
                        ->create([
                            'user_id' => User::factory()->create()->id,
                            'organization_id' => $org->id,
                            'invited_by_id' => $owner->id,
                        ]);

                    // Create a pending invitation
                    Membership::factory()
                        ->pending()
                        ->create([
                            'organization_id' => $org->id,
                            'invited_by_id' => $owner->id,
                        ]);
                });
        }
    }
}
