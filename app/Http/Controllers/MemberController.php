<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function invite(Organization $organization)
    {
        $this->authorize('inviteMembers', $organization);

        return Inertia::render('organizations/InviteMember', [
            'organization' => $organization,
            'roles' => [
                ['value' => 'admin', 'label' => 'Admin', 'description' => 'Can manage team and full invoice access'],
                ['value' => 'manager', 'label' => 'Manager', 'description' => 'Can create and edit invoices and clients'],
                ['value' => 'accountant', 'label' => 'Accountant', 'description' => 'Can view all invoices and manage payments'],
                ['value' => 'user', 'label' => 'User', 'description' => 'Can view assigned invoices only'],
            ],
        ]);
    }

    public function sendInvite(Request $request, Organization $organization)
    {
        $this->authorize('inviteMembers', $organization);

        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:admin,manager,accountant,user',
            'message' => 'nullable|string|max:500',
        ]);

        // Check if user already exists
        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser) {
            // Check if already a member
            if ($organization->members()->where('users.id', $existingUser->id)->exists()) {
                return redirect()->back()
                    ->with('error', 'This user is already a member of your organization.');
            }

            // Add existing user directly
            $organization->members()->attach($existingUser->id, [
                'role' => $validated['role'],
                'joined_at' => now(),
            ]);

            // TODO: Send notification email

            return redirect()->route('organizations.settings', $organization)
                ->with('success', "Invitation sent to {$validated['email']}");
        }

        // TODO: For new users, create invitation system in Phase 3.2
        // For now, just show a message
        return redirect()->back()
            ->with('info', 'User invitation system for new users will be available soon. Currently, only existing users can be added.');
    }

    public function updateRole(Request $request, Organization $organization, User $member)
    {
        $this->authorize('manageMembers', $organization);

        $validated = $request->validate([
            'role' => 'required|in:admin,manager,accountant,user',
        ]);

        // Can't change owner role
        $membership = $organization->members()->where('users.id', $member->id)->first();
        if ($membership && $membership->pivot->role === 'owner') {
            return redirect()->back()
                ->with('error', 'Cannot change the role of an owner.');
        }

        $organization->members()->updateExistingPivot($member->id, [
            'role' => $validated['role'],
        ]);

        return redirect()->back()
            ->with('success', 'Member role updated successfully.');
    }

    public function remove(Organization $organization, User $member)
    {
        $this->authorize('removeMembers', $organization);

        // Can't remove owner
        $membership = $organization->members()->where('users.id', $member->id)->first();
        if ($membership && $membership->pivot->role === 'owner') {
            return redirect()->back()
                ->with('error', 'Cannot remove the organization owner.');
        }

        // Can't remove yourself
        if ($member->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot remove yourself from the organization.');
        }

        $organization->members()->detach($member->id);

        return redirect()->back()
            ->with('success', 'Member removed successfully.');
    }
}
