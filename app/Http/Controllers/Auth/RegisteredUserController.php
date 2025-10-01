<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MembershipRole;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a personal organization for the new user
        $organization = Organization::create([
            'name' => $user->name."'s Organization",
            'slug' => Str::slug($user->name).'-'.Str::random(6),
            'email' => $user->email,
            'is_active' => true,
            'settings' => [
                'currency' => 'USD',
                'timezone' => config('app.timezone', 'UTC'),
                'date_format' => 'Y-m-d',
                'invoice_prefix' => 'INV',
            ],
        ]);

        // Create owner membership
        Membership::create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'role' => MembershipRole::Owner->value,
            'is_active' => true,
            'invitation_accepted_at' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Set the current organization
        set_current_organization($organization);

        return to_route('dashboard');
    }
}
