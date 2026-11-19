<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TokenRestablecimiento;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Restablecimiento de contrasena con token de un solo uso.
 *
 * Sustituye al envio por correo de Laravel: la tienda no tiene servidor de
 * correo configurado (MAIL_MAILER=log), por lo que aquel enlace terminaba
 * escrito en storage/logs en lugar de llegar al trabajador.
 *
 * Flujo: el trabajador pide el codigo al administrador, que lo genera desde
 * el modulo Usuarios y se lo entrega en persona; con ese codigo el trabajador
 * define su nueva contrasena. El codigo caduca a los 30 minutos y sirve una
 * sola vez.
 */
class RestablecerPasswordController extends Controller
{
    /** Intentos permitidos antes de bloquear temporalmente el formulario. */
    private const INTENTOS_MAXIMOS = 5;

    private const BLOQUEO_SEGUNDOS = 60;

    public function create(): View
    {
        return view('auth.restablecer', [
            'vigenciaMinutos' => TokenRestablecimiento::VIGENCIA_MINUTOS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'usuario' => ['required', 'string', 'max:255'],
            'token' => ['required', 'string', 'max:40'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'usuario.required' => 'Indique su usuario o correo.',
            'token.required' => 'Escriba el código que le entregó el administrador.',
        ]);

        $this->verificarIntentos($request);

        $identificador = Str::lower(trim($validated['usuario']));

        $usuario = User::where('usuario', $identificador)
            ->orWhere('email', $identificador)
            ->first();

        $token = $usuario && $usuario->estaActivo()
            ? TokenRestablecimiento::buscarVigente($usuario, $validated['token'])
            : null;

        // Un solo mensaje para todos los casos: no se revela si la cuenta
        // existe, si esta inactiva o si el codigo ya fue usado.
        if (! $token) {
            RateLimiter::hit($this->clave($request), self::BLOQUEO_SEGUNDOS);

            Log::warning('Intento fallido de restablecimiento de contraseña', [
                'usuario' => $identificador,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'token' => 'El código no es válido, ya se utilizó o venció. Solicite uno nuevo al administrador.',
            ]);
        }

        DB::transaction(function () use ($usuario, $token, $validated) {
            $usuario->forceFill([
                'password' => Hash::make($validated['password']),
                // Invalida las cookies de "recordar sesion" emitidas antes
                'remember_token' => Str::random(60),
            ])->save();

            $token->marcarUsado();

            // Por si quedara algun otro token emitido para esta cuenta
            TokenRestablecimiento::where('id_usuario', $usuario->id)
                ->whereNull('usado_en')
                ->update(['usado_en' => now()]);

            // Si alguien mas conocia la clave anterior, su sesion deja de valer
            $usuario->cerrarSesiones();
        });

        RateLimiter::clear($this->clave($request));

        Log::info('Contraseña restablecida con token de un solo uso', [
            'id_usuario' => $usuario->id,
            'id_token' => $token->id_token,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('login')
            ->with('status', 'Su contraseña fue actualizada. Ya puede iniciar sesión con la nueva contraseña.');
    }

    private function verificarIntentos(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->clave($request), self::INTENTOS_MAXIMOS)) {
            return;
        }

        throw ValidationException::withMessages([
            'token' => trans('auth.throttle', [
                'seconds' => $segundos = RateLimiter::availableIn($this->clave($request)),
                'minutes' => ceil($segundos / 60),
            ]),
        ]);
    }

    private function clave(Request $request): string
    {
        return 'restablecer|' . Str::lower((string) $request->input('usuario')) . '|' . $request->ip();
    }
}
