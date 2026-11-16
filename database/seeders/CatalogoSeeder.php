<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categorías Principales
        $categorias = [
            ['nombre' => 'Juegos de Mesa', 'descripcion' => 'Juegos de estrategia, familiares y cartas.'],
            ['nombre' => 'Figuras de Acción', 'descripcion' => 'Superhéroes, personajes de acción y coleccionables.'],
            ['nombre' => 'Muñecas y Accesorios', 'descripcion' => 'Muñecas clásicas, sets y accesorios infantiles.'],
            ['nombre' => 'Bloques y Construcción', 'descripcion' => 'Kits de armado, bloques plásticos y madera.'],
            ['nombre' => 'Vehículos y Pistas', 'descripcion' => 'Carros a control remoto, pistas y pistas de carreras.'],
        ];

        $catModels = [];
        foreach ($categorias as $cat) {
            $catModels[$cat['nombre']] = Categoria::updateOrCreate(
                ['nombre' => $cat['nombre']],
                ['descripcion' => $cat['descripcion'], 'estado' => 'activo']
            );
        }

        // 2. Catálogo de Juguetes (con stock variado, incluyendo stock crítico)
        $productos = [
            // Juegos de Mesa
            ['id_categoria' => $catModels['Juegos de Mesa']->id_categoria, 'sku' => 'JUG-001', 'nombre' => 'Monopoly Clásico Perú', 'precio' => 119.90, 'stock_actual' => 15, 'stock_minimo' => 5, 'estado' => 'disponible'],
            ['id_categoria' => $catModels['Juegos de Mesa']->id_categoria, 'sku' => 'JUG-002', 'nombre' => 'Jenga Clásico Original', 'precio' => 64.90, 'stock_actual' => 4, 'stock_minimo' => 5, 'estado' => 'stock bajo'], // Stock Crítico
            ['id_categoria' => $catModels['Juegos de Mesa']->id_categoria, 'sku' => 'JUG-003', 'nombre' => 'Uno Flip Cartas', 'precio' => 29.90, 'stock_actual' => 25, 'stock_minimo' => 8, 'estado' => 'disponible'],
            ['id_categoria' => $catModels['Juegos de Mesa']->id_categoria, 'sku' => 'JUG-004', 'nombre' => 'Ajedrez de Madera Profesional', 'precio' => 89.00, 'stock_actual' => 2, 'stock_minimo' => 3, 'estado' => 'stock bajo'], // Stock Crítico

            // Figuras de Acción
            ['id_categoria' => $catModels['Figuras de Acción']->id_categoria, 'sku' => 'JUG-005', 'nombre' => 'Spider-Man Titan Hero 30cm', 'precio' => 79.90, 'stock_actual' => 18, 'stock_minimo' => 6, 'estado' => 'disponible'],
            ['id_categoria' => $catModels['Figuras de Acción']->id_categoria, 'sku' => 'JUG-006', 'nombre' => 'Iron Man Avengers Articulado', 'precio' => 84.90, 'stock_actual' => 3, 'stock_minimo' => 5, 'estado' => 'stock bajo'], // Stock Crítico
            ['id_categoria' => $catModels['Figuras de Acción']->id_categoria, 'sku' => 'JUG-007', 'nombre' => 'Batman Batalla con Capa', 'precio' => 69.90, 'stock_actual' => 12, 'stock_minimo' => 4, 'estado' => 'disponible'],

            // Muñecas
            ['id_categoria' => $catModels['Muñecas y Accesorios']->id_categoria, 'sku' => 'JUG-008', 'nombre' => 'Barbie Fashionista Vestido Floral', 'precio' => 59.90, 'stock_actual' => 20, 'stock_minimo' => 5, 'estado' => 'disponible'],
            ['id_categoria' => $catModels['Muñecas y Accesorios']->id_categoria, 'sku' => 'JUG-009', 'nombre' => 'Baby Alive Hora del Baño', 'precio' => 149.00, 'stock_actual' => 6, 'stock_minimo' => 4, 'estado' => 'disponible'],
            ['id_categoria' => $catModels['Muñecas y Accesorios']->id_categoria, 'sku' => 'JUG-010', 'nombre' => 'Cry Babies Bebé Llorón Lala', 'precio' => 189.90, 'stock_actual' => 0, 'stock_minimo' => 3, 'estado' => 'agotado'], // Agotado

            // Bloques y Construcción
            ['id_categoria' => $catModels['Bloques y Construcción']->id_categoria, 'sku' => 'JUG-011', 'nombre' => 'LEGO Classic Caja Grande 790 pcs', 'precio' => 249.90, 'stock_actual' => 8, 'stock_minimo' => 3, 'estado' => 'disponible'],
            ['id_categoria' => $catModels['Bloques y Construcción']->id_categoria, 'sku' => 'JUG-012', 'nombre' => 'LEGO City Auto de Policía', 'precio' => 49.90, 'stock_actual' => 14, 'stock_minimo' => 5, 'estado' => 'disponible'],
            ['id_categoria' => $catModels['Bloques y Construcción']->id_categoria, 'sku' => 'JUG-013', 'nombre' => 'Mega Bloks Bolsa 80 Piezas', 'precio' => 74.90, 'stock_actual' => 10, 'stock_minimo' => 4, 'estado' => 'disponible'],

            // Vehículos y Pistas
            ['id_categoria' => $catModels['Vehículos y Pistas']->id_categoria, 'sku' => 'JUG-014', 'nombre' => 'Hot Wheels Pack 5 Autos', 'precio' => 45.00, 'stock_actual' => 30, 'stock_minimo' => 10, 'estado' => 'disponible'],
            ['id_categoria' => $catModels['Vehículos y Pistas']->id_categoria, 'sku' => 'JUG-015', 'nombre' => 'Pista Hot Wheels Giro Explosivo', 'precio' => 169.90, 'stock_actual' => 5, 'stock_minimo' => 4, 'estado' => 'disponible'],
            ['id_categoria' => $catModels['Vehículos y Pistas']->id_categoria, 'sku' => 'JUG-016', 'nombre' => 'Auto a Control Remoto Monster Truck', 'precio' => 129.00, 'stock_actual' => 1, 'stock_minimo' => 3, 'estado' => 'stock bajo'], // Stock Crítico
        ];

        foreach ($productos as $prod) {
            Producto::updateOrCreate(
                ['sku' => $prod['sku']],
                $prod
            );
        }

        // 3. Clientes de Ejemplo para el POS
        $clientes = [
            ['dni' => '12345678', 'nombres' => 'Juan Carlos Pérez Gómez', 'telefono' => '991234567', 'correo' => 'juan.perez@gmail.com'],
            ['dni' => '87654321', 'nombres' => 'María Elena Torres Flores', 'telefono' => '992345678', 'correo' => 'maria.torres@hotmail.com'],
            ['dni' => '45678912', 'nombres' => 'Carlos Alberto Quispe Mamani', 'telefono' => '993456789', 'correo' => 'carlos.quispe@outlook.com'],
            ['dni' => '00000000', 'nombres' => 'Clientes Varios / Público General', 'telefono' => null, 'correo' => null],
        ];

        foreach ($clientes as $cli) {
            Cliente::updateOrCreate(
                ['dni' => $cli['dni']],
                ['nombres' => $cli['nombres'], 'telefono' => $cli['telefono'], 'correo' => $cli['correo'], 'estado' => 'activo']
            );
        }
    }
}
