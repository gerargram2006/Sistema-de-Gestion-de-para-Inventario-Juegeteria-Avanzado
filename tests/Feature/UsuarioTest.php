<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->rol(User::ROL_ADMINISTRADOR)->create();
    }

    public function test_solo_el_administrador_accede_a_la_gestion_de_usuarios(): void
    {
        $this->actingAs($this->admin)->get('/usuarios')->assertOk()->assertSee($this->admin->name);

        foreach ([User::ROL_VENDEDOR, User::ROL_CAJERO, User::ROL_ALMACENERO] as $rol) {
            $usuario = User::factory()->rol($rol)->create();
            $this->actingAs($usuario)->get('/usuarios')->assertForbidden();
        }
    }

    public function test_el_administrador_crea_un_usuario_con_su_rol(): void
    {
        $this->actingAs($this->admin)->post('/usuarios', [
            'name' => 'Barry Valdivia',
            'usuario' => 'cajero2',
            'email' => 'cajero2@mundojuguete.pe',
            'telefono' => '987111222',
            'rol' => User::ROL_CAJERO,
            'password' => 'Caja2026*Segura',
            'password_confirmation' => 'Caja2026*Segura',
        ])->assertRedirect('/usuarios');

        $usuario = User::where('usuario', 'cajero2')->first();
        $this->assertSame(User::ROL_CAJERO, $usuario->rol);
        $this->assertTrue($usuario->estaActivo());
        $this->assertTrue(Hash::check('Caja2026*Segura', $usuario->password));

        // El nuevo cajero inicia sesion y solo entra a su modulo
        $this->post('/logout');
        $this->post('/login', ['email' => 'cajero2@mundojuguete.pe', 'password' => 'Caja2026*Segura']);
        $this->assertAuthenticatedAs($usuario);
        $this->get('/caja')->assertOk();
        $this->get('/ventas/pos')->assertForbidden();
    }

    public function test_no_se_aceptan_roles_desconocidos_ni_usuarios_repetidos(): void
    {
        $existente = User::factory()->create(['usuario' => 'vendedor1']);

        $this->actingAs($this->admin)->post('/usuarios', [
            'name' => 'Intruso',
            'usuario' => 'vendedor1',
            'email' => $existente->email,
            'rol' => 'superusuario',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(['usuario', 'email', 'rol']);
    }

    public function test_editar_sin_contrasena_conserva_la_actual(): void
    {
        $vendedor = User::factory()->rol(User::ROL_VENDEDOR)->create(['usuario' => 'vendedor9']);
        $hashAnterior = $vendedor->password;

        $this->actingAs($this->admin)->put("/usuarios/{$vendedor->id}", [
            'name' => 'Vendedor Promovido',
            'usuario' => 'vendedor9',
            'email' => $vendedor->email,
            'rol' => User::ROL_ALMACENERO,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect('/usuarios');

        $vendedor->refresh();
        $this->assertSame(User::ROL_ALMACENERO, $vendedor->rol);
        $this->assertSame('Vendedor Promovido', $vendedor->name);
        $this->assertSame($hashAnterior, $vendedor->password);
    }

    public function test_dar_de_baja_impide_el_acceso_y_reactivar_lo_devuelve(): void
    {
        $cajero = User::factory()->rol(User::ROL_CAJERO)->create();

        $this->actingAs($this->admin)->patch("/usuarios/{$cajero->id}/estado");
        $this->assertFalse($cajero->fresh()->estaActivo());

        $this->actingAs($this->admin)->patch("/usuarios/{$cajero->id}/estado");
        $this->assertTrue($cajero->fresh()->estaActivo());
    }

    public function test_el_administrador_no_puede_bloquearse_a_si_mismo(): void
    {
        $this->actingAs($this->admin)
            ->patch("/usuarios/{$this->admin->id}/estado")
            ->assertSessionHasErrors('estado');

        $this->actingAs($this->admin)->put("/usuarios/{$this->admin->id}", [
            'name' => $this->admin->name,
            'usuario' => 'admin9',
            'email' => $this->admin->email,
            'rol' => User::ROL_VENDEDOR,
        ])->assertSessionHasErrors('rol');

        $this->admin->refresh();
        $this->assertTrue($this->admin->estaActivo());
        $this->assertTrue($this->admin->esAdministrador());
    }
}
