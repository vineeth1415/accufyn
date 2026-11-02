<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $installedFile = storage_path('installed');
        $hasInstalledFile = file_exists($installedFile);
        $hasDatabaseTables = false;

        if ($hasInstalledFile) {
            try {
                $hasDatabaseTables = \Illuminate\Support\Facades\Schema::hasTable('companies')
                                  && \Illuminate\Support\Facades\Schema::hasTable('users');
            } catch (\Throwable $e) {
                $hasDatabaseTables = false;
            }
        }

        $isInstalled = $hasInstalledFile && $hasDatabaseTables;
        $isInstallRoute = $request->is('install*') || $request->is('install');

        // If not installed and not on install route or assets, redirect to install wizard
        if (!$isInstalled && !$isInstallRoute && !$request->is('build*') && !$request->is('assets*') && !$request->is('favicon.ico')) {
            return redirect()->route('install.index');
        }

        // If already installed and trying to access install wizard, redirect to login unless on complete screen
        if ($isInstalled && $isInstallRoute && !$request->is('install/complete')) {
            return redirect()->route('login')->with('info', 'WebotApp Accounting is already installed.');
        }

        return $next($request);
    }
}
