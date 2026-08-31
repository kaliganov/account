<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsApproved;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'approved' => EnsureUserIsApproved::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'download-invoices-archive',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson() || ! $request->isMethod('GET')) {
                return null;
            }

            return redirect()
                ->route('home')
                ->withErrors([
                    'status' => 'Это действие нужно запускать кнопкой в интерфейсе, а не обновлением страницы или прямой ссылкой.',
                ]);
        });
    })->create();
