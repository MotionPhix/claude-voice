<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationIsSet
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users
        if ($request->user()) {
            // Ensure the user has an organization set
            ensure_organization();

            // If still no organization, redirect to organization selection
            if (! current_organization_id() && ! $request->routeIs('organizations.*')) {
                return redirect()->route('organizations.select');
            }
        }

        return $next($request);
    }
}
