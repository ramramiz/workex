<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'       => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\RestrictToRoomWork::class,
            \App\Http\Middleware\VerifyMenuPermission::class,
        ]);
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->expectsJson() || 
                $request->ajax() || 
                $request->isXmlHttpRequest() ||
                $request->headers->get('Sec-Fetch-Dest') === 'empty' ||
                $request->method() !== 'GET' ||
                $request->is(
                    '*/unread-count',
                    '*/unread-counts',
                    '*/unified-list',
                    '*/updates',
                    'live-status/data',
                    'grammar/correct',
                    'ai/correct',
                    'chat/tasks/*',
                    'chat/employees/*',
                    'direct-chat/messages/*',
                    'direct-chat/read/*',
                    'mailbox/fetch-new',
                    'tasks/*/update-status',
                    'bugs/*/update-status'
                )
            ) {
                abort(401);
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
