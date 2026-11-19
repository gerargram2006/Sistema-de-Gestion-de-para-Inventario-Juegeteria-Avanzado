<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad para todas las respuestas del sistema (RNF-03).
 *
 * Reducen el impacto de ataques habituales sobre el navegador del cajero o
 * del vendedor sin cambiar el funcionamiento de la aplicacion.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // El sistema no debe poder incrustarse en un iframe de otro sitio
        // (evita el robo de clics sobre el POS o la caja).
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // El navegador respeta el tipo declarado y no "adivina" otro.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // No se filtra la URL interna (con identificadores de venta o cliente)
        // al salir hacia otro sitio.
        $response->headers->set('Referrer-Policy', 'same-origin');

        // La aplicacion no necesita camara, microfono ni ubicacion.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Las pantallas con datos de ventas y clientes no quedan en la cache
        // del navegador: al cerrar sesion, el boton "atras" no las muestra.
        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        }

        return $response;
    }
}
