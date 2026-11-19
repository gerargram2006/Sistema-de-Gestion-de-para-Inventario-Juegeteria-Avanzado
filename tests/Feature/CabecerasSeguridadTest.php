<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cabeceras de seguridad enviadas en todas las respuestas (RNF-03).
 */
class CabecerasSeguridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_paginas_publicas_envian_las_cabeceras_de_seguridad(): void
    {
        $respuesta = $this->get('/login');

        $respuesta->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'same-origin');
    }

    public function test_las_paginas_con_sesion_no_quedan_en_la_cache_del_navegador(): void
    {
        $usuario = User::factory()->rol(User::ROL_ADMINISTRADOR)->create();

        $this->actingAs($usuario)->get('/dashboard')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');

        $this->assertStringContainsString(
            'no-store',
            $this->actingAs($usuario)->get('/dashboard')->headers->get('Cache-Control')
        );
    }
}
