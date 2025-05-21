<?php

use BezhanSalleh\FilamentExceptions\Facades\FilamentExceptions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Laravel\Sanctum\Exceptions\MissingScopeException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Exception|Throwable $e) {
            FilamentExceptions::report($e);
        });
        Integration::handles($exceptions);
        // Tratar token ausente
        // Resposta para token ausente/inválido (401)
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Autenticação requerida. Por favor, envie um token Bearer válido.',
                ], 401);
            }
        });

        // Resposta para escopos/abilidades faltantes (403)
        $exceptions->renderable(function (MissingScopeException|MissingAbilityException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Você não tem permissão para acessar este recurso.',
                ], 403);
            }
            abort(403); // Fallback para rotas web
        });
    })->create();
