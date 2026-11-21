<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        // RUC ficticios, solo para desarrollo
        $proveedores = [
            ['razon_social' => 'Mattel Perú S.A.', 'ruc' => '20100000001', 'telefono' => '01 555 0101', 'email' => 'ventas@mattel.test', 'activo' => true],
            ['razon_social' => 'Distribuidora Juguetes del Sur S.A.C.', 'ruc' => '20100000002', 'telefono' => '054 555 0102', 'email' => 'pedidos@juguetesdelsur.test', 'activo' => true],
            ['razon_social' => 'Importaciones Lúdica E.I.R.L.', 'ruc' => '20100000003', 'telefono' => '01 555 0103', 'email' => null, 'activo' => false],
        ];

        foreach ($proveedores as $datos) {
            Proveedor::updateOrCreate(['ruc' => $datos['ruc']], $datos);
        }
    }
}
