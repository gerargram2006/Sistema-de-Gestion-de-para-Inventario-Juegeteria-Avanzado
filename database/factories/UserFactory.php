<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'rol' => User::ROL_VENDEDOR,
            'estado' => User::ESTADO_ACTIVO,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Usuario con un rol operativo especifico (administrador, vendedor, cajero, almacenero).
     */
    public function rol(string $rol): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => $rol,
        ]);
    }

    /**
     * Usuario dado de baja: no puede iniciar sesion.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => User::ESTADO_INACTIVO,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
