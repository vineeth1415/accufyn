<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// Auto-create required cache, view, and session storage directories if missing
$basePath = dirname(__DIR__);
$requiredStorageDirs = [
    $basePath . '/storage/framework/views',
    $basePath . '/storage/framework/cache',
    $basePath . '/storage/framework/cache/data',
    $basePath . '/storage/framework/sessions',
    $basePath . '/storage/logs',
    $basePath . '/bootstrap/cache',
];
foreach ($requiredStorageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// Auto-create .env from .env.example if missing
if (!file_exists($basePath . '/.env') && file_exists($basePath . '/.env.example')) {
    @copy($basePath . '/.env.example', $basePath . '/.env');
}

$app = Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\CheckInstallation::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'install/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// Fail-safe storage engine: If permissions on local storage prevent web server (daemon/www-data) from writing,
// automatically switch storage to a writable system temp directory so 500 error NEVER occurs.
$viewsDir = $basePath . '/storage/framework/views';
$logsDir = $basePath . '/storage/logs';
if (!is_writable($viewsDir) || !is_writable($logsDir)) {
    $fallbackStorage = sys_get_temp_dir() . '/webotapp_accounts_' . substr(md5($basePath), 0, 8);
    $fallbackDirs = [
        $fallbackStorage,
        $fallbackStorage . '/framework',
        $fallbackStorage . '/framework/views',
        $fallbackStorage . '/framework/cache',
        $fallbackStorage . '/framework/cache/data',
        $fallbackStorage . '/framework/sessions',
        $fallbackStorage . '/logs',
        $fallbackStorage . '/app',
        $fallbackStorage . '/app/public',
    ];
    foreach ($fallbackDirs as $fDir) {
        if (!is_dir($fDir)) {
            @mkdir($fDir, 0777, true);
        }
        @chmod($fDir, 0777);
    }

    if (file_exists($basePath . '/storage/version.json') && !file_exists($fallbackStorage . '/version.json')) {
        @copy($basePath . '/storage/version.json', $fallbackStorage . '/version.json');
    }
    if (file_exists($basePath . '/storage/installed') && !file_exists($fallbackStorage . '/installed')) {
        @copy($basePath . '/storage/installed', $fallbackStorage . '/installed');
    }

    $app->useStoragePath($fallbackStorage);
}

return $app;
