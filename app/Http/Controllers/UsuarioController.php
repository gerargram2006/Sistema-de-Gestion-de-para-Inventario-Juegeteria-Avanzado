<?php

namespace App\Http\Controllers;

use App\Models\TokenRestablecimiento;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Gestion de usuarios y roles (solo administrador).
 *
 * Reemplaza al registro publico: el administrador crea las cuentas de los
 * trabajadores, les asigna su rol operativo y las da de baja (inactivo)
 * sin borrar su historial de ventas, cobros y compras.
 */
class UsuarioController extends Controller
{
    public const ROLES = [
        User::ROL_ADMINISTRADOR => 'Administrador',
        User::ROL_VENDEDOR => 'Vendedor',
        User::ROL_CAJERO => 'Cajero',
        User::ROL_ALMACENERO => 'Encargado de almacén',
    ];

    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                    ->orWhere('usuario', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }

        $usuarios = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles = self::ROLES;

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas());

        User::create([
            ...$validated,
            'estado' => User::ESTADO_ACTIVO,
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$validated['name']} creado con el rol " . self::ROLES[$validated['rol']] . '.');
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate($this->reglas($usuario));

        // El administrador no puede quitarse su propio rol: se quedaria sin acceso a este modulo
        if ($usuario->is($request->user()) && $validated['rol'] !== User::ROL_ADMINISTRADOR) {
            return back()->withErrors(['rol' => 'No puede quitarse a sí mismo el rol de administrador.']);
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $usuario->update($validated);

        return redirect()->route('usuarios.index')
            ->with('success', "Datos de {$usuario->name} actualizados correctamente.");
    }

    /**
     * Activa o da de baja a un usuario. Una cuenta inactiva no puede iniciar
     * sesion, pero su historial se conserva.
     */
    public function cambiarEstado(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->withErrors(['estado' => 'No puede desactivar su propia cuenta.']);
        }

        $usuario->update([
            'estado' => $usuario->estaActivo() ? User::ESTADO_INACTIVO : User::ESTADO_ACTIVO,
        ]);

        $accion = $usuario->estaActivo() ? 'activado' : 'dado de baja';

        return back()->with('success', "Usuario {$usuario->name} {$accion}.");
    }

    /**
     * Genera un token de un solo uso para que el trabajador defina una nueva
     * contrasena. El codigo se muestra una unica vez al administrador, que lo
     * entrega en persona; en la base de datos solo queda su hash.
     */
    public function generarToken(Request $request, User $usuario): RedirectResponse
    {
        if (! $usuario->estaActivo()) {
            return back()->withErrors([
                'token' => "La cuenta de {$usuario->name} está inactiva. Reactívela antes de generar un código.",
            ]);
        }

        $codigo = TokenRestablecimiento::generarPara($usuario, $request->user());

        Log::info('Token de restablecimiento generado', [
            'id_usuario' => $usuario->id,
            'id_admin' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        return back()->with('token_generado', [
            'usuario' => $usuario->name,
            'codigo' => TokenRestablecimiento::formatear($codigo),
            'minutos' => TokenRestablecimiento::VIGENCIA_MINUTOS,
        ]);
    }

    /**
     * Reglas compartidas entre crear y editar.
     */
    private function reglas(?User $usuario = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'usuario' => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'usuario')->ignore($usuario)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario)],
            'telefono' => ['nullable', 'string', 'max:15'],
            'rol' => ['required', Rule::in(array_keys(self::ROLES))],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ];
    }
}
