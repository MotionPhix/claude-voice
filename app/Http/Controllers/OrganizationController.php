<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Organization::class);

        $organizations = auth()->user()->organizations()
            ->withPivot('role', 'joined_at')
            ->get();

        return Inertia::render('organizations/Index', [
            'organizations' => $organizations,
        ]);
    }

    public function select()
    {
        $organizations = auth()->user()->organizations()
            ->withPivot('role', 'joined_at')
            ->get();

        // If user has only one organization, automatically select it
        if ($organizations->count() === 1) {
            set_current_organization($organizations->first());
            return redirect()->route('dashboard');
        }

        // If user has no organizations, redirect to create
        if ($organizations->count() === 0) {
            return redirect()->route('organizations.create');
        }

        return Inertia::render('organizations/Select', [
            'organizations' => $organizations,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Organization::class);

        return Inertia::render('organizations/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Organization::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $organization = Organization::create($validated);

        // Add creator as owner
        $organization->members()->attach(auth()->id(), [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        // Set as current organization
        set_current_organization($organization);

        return redirect()->route('dashboard')
            ->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        $this->authorize('view', $organization);

        $organization->load(['members' => function ($query) {
            $query->withPivot('role', 'joined_at');
        }]);

        return Inertia::render('organizations/Show', [
            'organization' => $organization,
        ]);
    }

    public function settings(Organization $organization)
    {
        $this->authorize('updateSettings', $organization);

        $members = $organization->members()
            ->withPivot(['role', 'is_active'])
            ->get()
            ->map(function ($member) {
                $roleLabels = [
                    'owner' => 'Owner',
                    'admin' => 'Admin',
                    'manager' => 'Manager',
                    'accountant' => 'Accountant',
                    'user' => 'User',
                ];

                return [
                    'id' => $member->id,
                    'uuid' => $member->uuid,
                    'name' => $member->name,
                    'email' => $member->email,
                    'pivot' => [
                        'role' => $member->pivot->role,
                        'role_label' => $roleLabels[$member->pivot->role] ?? ucfirst($member->pivot->role),
                        'is_active' => $member->pivot->is_active,
                    ],
                ];
            });

        return Inertia::render('organizations/Settings', [
            'organization' => array_merge($organization->toArray(), [
                'members' => $members,
            ]),
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $organization->update($validated);

        return redirect()->back()
            ->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        $this->authorize('delete', $organization);

        // Prevent deletion if has data
        if ($organization->invoices()->count() > 0 || $organization->clients()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete organization with existing data. Please delete all invoices and clients first.');
        }

        $organization->delete();

        // Set another organization as current if available
        ensure_organization();

        return redirect()->route('dashboard')
            ->with('success', 'Organization deleted successfully.');
    }

    public function switch(Request $request, Organization $organization)
    {
        // Verify user belongs to this organization
        if (! auth()->user()->belongsToOrganization($organization)) {
            abort(403, 'You do not have access to this organization.');
        }

        set_current_organization($organization);

        return redirect()->back()
            ->with('success', "Switched to {$organization->name}");
    }
}
