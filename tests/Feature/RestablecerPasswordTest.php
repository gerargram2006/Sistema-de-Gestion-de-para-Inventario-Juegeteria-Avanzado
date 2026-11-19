<?php

namespace Tests\Feature;

use App\Models\TokenRestablecimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Restablecimiento de contrasena con token de un solo uso.
 */
class RestablecerPasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->rol(User::ROL_ADMINISTRADOR)->create();
    }

    /** Genera un token y devuelve el codigo en claro, como lo veria el administrador. */
    private function generarCodigo(User $usuario): string
    {
        $respuesta = $this->actingAs($this->admin)->post("/usuarios/{$usuario->id}/token");
        $respuesta->assertRedirect();

        $codigo = session('token_generado')['codigo'];

        // El trabajador canjea el codigo sin sesion iniciada
        Auth::logout();

        return $codigo;
    }

    public function test_el_administrador_genera_un_codigo_y_la_base_solo_guarda_su_hash(): void
    {
        $cajero = User::factory()->rol(User::ROL_CAJERO)->create();

        $codigo = $this->generarCodigo($cajero);

        $this->assertMatchesRegularExpression('/^[A-Z2-9]{4}-[A-Z2-9]{4}-[A-Z2-9]{4}$/', $codigo);

        $token = TokenRestablecimiento::where('id_usuario', $cajero->id)->firstOrFail();
        $this->assertNotSame($codigo, $token->token_hash);
        $this->assertTrue(Hash::check(TokenRestablecimiento::normalizar($codigo), $token->token_hash));
        $this->assertSame($this->admin->id, $token->id_admin);
        $this->assertNull($token->usado_en);
    }

    public function test_el_trabajador_define_su_nueva_contrasena_con_el_codigo(): void
    {
        $cajero = User::factory()->rol(User::ROL_CAJERO)->create(['usuario' => 'cajero1']);
        $codigo = $this->generarCodigo($cajero);

        $this->post('/restablecer', [
            'usuario' => 'cajero1',
            'token' => $codigo,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertRedirect('/login')->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NuevaClave2026', $cajero->refresh()->password));
        $this->assertNotNull(TokenRestablecimiento::where('id_usuario', $cajero->id)->first()->usado_en);
    }

    public function test_el_codigo_no_sirve_dos_veces(): void
    {
        $cajero = User::factory()->rol(User::ROL_CAJERO)->create(['usuario' => 'cajero2']);
        $codigo = $this->generarCodigo($cajero);

        $datos = [
            'usuario' => 'cajero2',
            'token' => $codigo,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ];

        $this->post('/restablecer', $datos)->assertSessionHasNoErrors();

        // Segundo intento con el mismo codigo: ya fue consumido
        $this->post('/restablecer', [...$datos, 'password' => 'OtraClave2026', 'password_confirmation' => 'OtraClave2026'])
            ->assertSessionHasErrors('token');

        $this->assertTrue(Hash::check('NuevaClave2026', $cajero->refresh()->password));
    }

    public function test_un_codigo_vencido_es_rechazado(): void
    {
        $cajero = User::factory()->rol(User::ROL_CAJERO)->create(['usuario' => 'cajero3']);
        $codigo = $this->generarCodigo($cajero);
        $claveAnterior = $cajero->password;

        TokenRestablecimiento::where('id_usuario', $cajero->id)
            ->update(['expira_en' => now()->subMinute()]);

        $this->post('/restablecer', [
            'usuario' => 'cajero3',
            'token' => $codigo,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertSessionHasErrors('token');

        $this->assertSame($claveAnterior, $cajero->refresh()->password);
    }

    public function test_generar_un_codigo_nuevo_anula_el_anterior(): void
    {
        $cajero = User::factory()->rol(User::ROL_CAJERO)->create(['usuario' => 'cajero4']);
        $primero = $this->generarCodigo($cajero);
        $segundo = $this->generarCodigo($cajero);

        $this->post('/restablecer', [
            'usuario' => 'cajero4',
            'token' => $primero,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertSessionHasErrors('token');

        $this->post('/restablecer', [
            'usuario' => 'cajero4',
            'token' => $segundo,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertSessionHasNoErrors();
    }

    public function test_el_codigo_de_un_trabajador_no_sirve_para_otra_cuenta(): void
    {
        $cajero = User::factory()->rol(User::ROL_CAJERO)->create(['usuario' => 'cajero5']);
        $vendedor = User::factory()->rol(User::ROL_VENDEDOR)->create(['usuario' => 'vendedor5']);
        $codigo = $this->generarCodigo($cajero);
        $claveVendedor = $vendedor->password;

        $this->post('/restablecer', [
            'usuario' => 'vendedor5',
            'token' => $codigo,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertSessionHasErrors('token');

        $this->assertSame($claveVendedor, $vendedor->refresh()->password);
    }

    public function test_no_se_genera_codigo_para_una_cuenta_inactiva(): void
    {
        $inactivo = User::factory()->rol(User::ROL_CAJERO)->inactivo()->create();

        $this->actingAs($this->admin)->post("/usuarios/{$inactivo->id}/token")
            ->assertSessionHasErrors('token');

        $this->assertSame(0, TokenRestablecimiento::where('id_usuario', $inactivo->id)->count());
    }

    public function test_solo_el_administrador_puede_generar_codigos(): void
    {
        $cajero = User::factory()->rol(User::ROL_CAJERO)->create();
        $vendedor = User::factory()->rol(User::ROL_VENDEDOR)->create();

        $this->actingAs($vendedor)->post("/usuarios/{$cajero->id}/token")->assertForbidden();
        $this->assertSame(0, TokenRestablecimiento::count());
    }

    public function test_la_politica_rechaza_contrasenas_debiles(): void
    {
        $cajero = User::factory()->rol(User::ROL_CAJERO)->create(['usuario' => 'cajero6']);
        $codigo = $this->generarCodigo($cajero);

        $this->post('/restablecer', [
            'usuario' => 'cajero6',
            'token' => $codigo,
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])->assertSessionHasErrors('password');

        // El codigo sigue disponible: no se consume si la contrasena no pasa
        $this->assertNull(TokenRestablecimiento::where('id_usuario', $cajero->id)->first()->usado_en);
    }

    public function test_al_restablecer_se_cierran_las_sesiones_abiertas_en_otros_equipos(): void
    {
        config(['session.driver' => 'database']);

        $cajero = User::factory()->rol(User::ROL_CAJERO)->create(['usuario' => 'cajero7']);
        $codigo = $this->generarCodigo($cajero);

        DB::table('sessions')->insert([
            'id' => 'sesion-en-otro-equipo',
            'user_id' => $cajero->id,
            'ip_address' => '192.168.1.50',
            'user_agent' => 'navegador de prueba',
            'payload' => '',
            'last_activity' => time(),
        ]);

        $this->post('/restablecer', [
            'usuario' => 'cajero7',
            'token' => $codigo,
            'password' => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, DB::table('sessions')->where('user_id', $cajero->id)->count());
    }

    public function test_el_formulario_publico_de_restablecimiento_esta_disponible(): void
    {
        $this->get('/restablecer')->assertOk()->assertSee('Restablecer contraseña');
    }

    public function test_el_envio_de_enlaces_por_correo_quedo_deshabilitado(): void
    {
        $this->get('/forgot-password')->assertNotFound();
        $this->post('/reset-password')->assertNotFound();
    }
}
