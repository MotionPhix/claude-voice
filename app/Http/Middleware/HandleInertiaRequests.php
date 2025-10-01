<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        // Format user organizations with role labels
        $userOrgs = user_organizations();
        $roleLabels = [
            'owner' => 'Owner',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'accountant' => 'Accountant',
            'user' => 'User',
        ];

        $formattedOrgs = $userOrgs->map(function ($org) use ($roleLabels) {
            return [
                'id' => $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
                'pivot' => [
                    'role' => $org->pivot->role ?? 'user',
                    'role_label' => $roleLabels[$org->pivot->role ?? 'user'] ?? 'User',
                ],
            ];
        });

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
                'currentOrganization' => current_organization(),
                'currentMembership' => current_membership(),
                'userOrganizations' => $formattedOrgs,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
