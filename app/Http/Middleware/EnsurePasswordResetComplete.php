<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordResetComplete
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user?->must_reset_password) {
            return $next($request);
        }

        if ($request->routeIs('password.force.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        return redirect()->route('password.force.show');
    }
}
