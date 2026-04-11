<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = null): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $roles = $user->getRoleNames();

            if ($roles->isEmpty()) {
                // Should not happen with migrated data, but safe to logout
                Auth::logout();
                return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki role.');
            }

            if (!$request->session()->has('active_role')) {
                $request->session()->put('active_role', $roles->first());
            }

            // Optional: If a specific role is required by middleware parameter
            if ($role && $request->session()->get('active_role') !== $role) {
                // You might want to redirect to a "switcher" or just deny
                abort(403, 'Anda tidak memiliki akses dengan role ini.');
            }
        }

        return $next($request);
    }
}
