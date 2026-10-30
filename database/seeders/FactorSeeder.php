<?php

namespace Database\Seeders;

use App\Models\Factor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FactorSeeder extends Seeder
{
    /**
     * Los siete factores del diccionario del reto (ids 1 a 7).
     */
    public function run(): void
    {
        Factor::insert([
            ['id' => 1, 'codigo' => 'MONTO', 'nombre' => 'Monto', 'descripcion' => 'Monto de la operación frente al perfil del cliente.'],
            ['id' => 2, 'codigo' => 'FRECUENCIA', 'nombre' => 'Frecuencia', 'descripcion' => 'Frecuencia de las operaciones frente a lo habitual.'],
            ['id' => 3, 'codigo' => 'PRODUCTO', 'nombre' => 'Producto', 'descripcion' => 'Riesgo del producto financiero utilizado.'],
            ['id' => 4, 'codigo' => 'CANAL', 'nombre' => 'Canal', 'descripcion' => 'Canal por el que se realiza la operación.'],
            ['id' => 5, 'codigo' => 'JURISDICCION', 'nombre' => 'Jurisdicción', 'descripcion' => 'Riesgo del país o zona de la operación.'],
            ['id' => 6, 'codigo' => 'PERFIL', 'nombre' => 'Perfil del cliente', 'descripcion' => 'Coherencia de la operación con el perfil del cliente.'],
            ['id' => 7, 'codigo' => 'PEP_SANC', 'nombre' => 'PEP / Sanciones', 'descripcion' => 'Vínculo con PEP o coincidencia en listas de sanciones.'],
        ]);
    }
}
