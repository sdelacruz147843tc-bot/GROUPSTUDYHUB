<?php

use App\Http\Middleware\EnsureStudyHubRole;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->alias([
            'studyhub.role' => EnsureStudyHubRole::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $e instanceof ValidationException) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $title = $status >= 500 ? 'Application Error' : 'Request Error';
            $message = config('app.debug')
                ? ($e->getMessage() ?: 'An unexpected error occurred.')
                : ($status === 404 ? 'The page you requested could not be found.' : 'Something went wrong while loading this page.');

            $details = config('app.debug')
                ? sprintf(
                    '<p><strong>%s</strong></p><p>%s:%d</p>',
                    e($e::class),
                    e($e->getFile()),
                    $e->getLine(),
                )
                : '';

            $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$status} {$title}</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f6f7fb;
            color: #172033;
        }
        main {
            max-width: 720px;
            margin: 8vh auto;
            padding: 32px;
        }
        .card {
            background: #fff;
            border: 1px solid #d9e0ee;
            border-radius: 16px;
            box-shadow: 0 14px 40px rgba(23, 32, 51, 0.08);
            padding: 28px;
        }
        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: #e8eefc;
            color: #274690;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        h1 {
            margin: 16px 0 8px;
            font-size: 32px;
            line-height: 1.15;
        }
        p {
            margin: 0 0 12px;
            line-height: 1.6;
        }
        .details {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5eaf4;
            color: #475069;
        }
    </style>
</head>
<body>
    <main>
        <div class="card">
            <span class="badge">{$status}</span>
            <h1>{$title}</h1>
            <p>{$message}</p>
            <div class="details">
                {$details}
            </div>
        </div>
    </main>
</body>
</html>
HTML;

            return response($html, $status)->header('Content-Type', 'text/html; charset=UTF-8');
        });
    })->create();
