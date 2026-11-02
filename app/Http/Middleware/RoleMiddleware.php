<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact support.');
        }

        // Flatten any comma-separated roles (e.g., 'admin,staff')
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $allowedRoles[] = strtoupper(trim($r));
            }
        }

        $userRole = strtoupper($user->role ?? '');

        // SUPER_ADMIN (Top Owner) has master access to everything
        if (in_array($userRole, ['SUPER_ADMIN', 'SUPERADMIN'])) {
            return $next($request);
        }

        // ADMIN has access to everything
        if ($userRole === 'ADMIN') {
            return $next($request);
        }

        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        // Auditor specific: redirect gracefully to Financial Reports
        if ($userRole === 'AUDITOR') {
            return redirect()->route('reports.profit_loss')->with('info', 'Auditor Notice: You have view & Excel export privileges for Financial Reports only.');
        }

        abort(403, 'Unauthorized action. You do not have permission to access this resource.');
    }
}
