<?php

namespace Database\Factories;

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership>
 */
class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'organization_id' => Organization::factory(),
            'role' => MembershipRole::User->value,
            'is_active' => true,
            'invited_email' => null,
            'invitation_token' => null,
            'invitation_sent_at' => null,
            'invitation_accepted_at' => now(),
            'invitation_expires_at' => null,
            'invited_by_id' => null,
        ];
    }

    /**
     * Indicate that the membership is for an owner.
     */
    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MembershipRole::Owner->value,
        ]);
    }

    /**
     * Indicate that the membership is for an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MembershipRole::Admin->value,
        ]);
    }

    /**
     * Indicate that the membership is for a manager.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MembershipRole::Manager->value,
        ]);
    }

    /**
     * Indicate that the membership is for an accountant.
     */
    public function accountant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MembershipRole::Accountant->value,
        ]);
    }

    /**
     * Indicate that the membership is for a user.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MembershipRole::User->value,
        ]);
    }

    /**
     * Indicate that the membership is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the membership is a pending invitation.
     */
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            $email = fake()->unique()->safeEmail();

            return [
                'user_id' => null,
                'invited_email' => $email,
                'invitation_token' => Str::random(64),
                'invitation_sent_at' => now(),
                'invitation_accepted_at' => null,
                'invitation_expires_at' => now()->addDays(7),
                'invited_by_id' => User::factory(),
            ];
        });
    }

    /**
     * Indicate that the invitation has expired.
     */
    public function expired(): static
    {
        return $this->pending()->state(fn (array $attributes) => [
            'invitation_expires_at' => now()->subDay(),
        ]);
    }
}
