<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Gerar Camma (Administrador)',
                'usuario' => 'admin1',
                'email' => 'admin@mundojuguete.pe',
                'telefono' => '987654321',
                'rol' => User::ROL_ADMINISTRADOR,
                'estado' => 'activo',
                'password' => Hash::make('Admin123*'),
            ],
            [
                'name' => 'Rodrigo Vendedor',
                'usuario' => 'vendedor1',
                'email' => 'vendedor@mundojuguete.pe',
                'telefono' => '987654322',
                'rol' => User::ROL_VENDEDOR,
                'estado' => 'activo',
                'password' => Hash::make('Venta123*'),
            ],
            [
                'name' => 'Samuel Cajero',
                'usuario' => 'cajero1',
                'email' => 'cajero@mundojuguete.pe',
                'telefono' => '987654323',
                'rol' => User::ROL_CAJERO,
                'estado' => 'activo',
                'password' => Hash::make('Caja123*'),
            ],
            [
                'name' => 'Hugo Almacenero',
                'usuario' => 'almacen1',
                'email' => 'almacen@mundojuguete.pe',
                'telefono' => '987654324',
                'rol' => User::ROL_ALMACENERO,
                'estado' => 'activo',
                'password' => Hash::make('Almacen123*'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
