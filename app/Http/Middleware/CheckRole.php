<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
            return redirect()->route('login');
        }

        // Si no se especificaron roles, continuar
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los roles permitidos
        if ($user->tieneRol(...$roles)) {
            return $next($request);
        }

        // Si no tiene el rol, devolver 403 (JSON para AJAX/fetch, HTML para navegador)
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Acceso no autorizado para su rol operativo.'], 403);
        }
        abort(403, 'Acceso no autorizado para su rol operativo (' . ($user->rol ?? 'sin rol') . ').');
    }
}
