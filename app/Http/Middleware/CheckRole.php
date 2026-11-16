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

        // Si no tiene el rol, abortar con 403 o redirigir con alerta
        abort(403, 'Acceso no autorizado para su rol operativo (' . ($user->rol ?? 'sin rol') . ').');
    }
}
