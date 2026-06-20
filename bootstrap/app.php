<?php

declare(strict_types=1);

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        App\Providers\RepositoryServiceProvider::class,
    ])
    ->create();

// Vercel serverless: storage lives in /tmp (read-only filesystem except /tmp)
if (!empty($_ENV['VERCEL'])) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
