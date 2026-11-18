<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pantalla_de_registro_publico_no_existe(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_un_visitante_no_puede_crearse_una_cuenta(): void
    {
        $this->post('/register', [
            'name' => 'Usuario Externo',
            'email' => 'externo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'externo@example.com']);
    }
}
