<?php

use App\Exceptions\ReglaNegocioException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Toda respuesta bajo /api es JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*') || $request->expectsJson(),
        );

        // Evita registrar errores de reglas de negocio.
        $exceptions->dontReport([
            ReglaNegocioException::class,
        ]);

        // Convierte las excepciones de negocio en respuestas HTTP 409.
        $exceptions->render(
            function (ReglaNegocioException $exception) {
                return response()->json([
                    'mensaje' => $exception->getMessage(),
                    'codigo' => 'REGLA_NEGOCIO',
                ], 409);
            }
        );
    })
    ->create();
