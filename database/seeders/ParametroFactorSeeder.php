<?php

namespace Database\Seeders;

use App\Models\ParametroFactor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParametroFactorSeeder extends Seeder
{
    /**
     * Pesos de v1.0 (suman 100): Monto 20, Frecuencia 15, Producto 10, Canal 10, Jurisdicción 20, Perfil 15, PEP/Sanciones 10.
     */
    public function run(): void
    {
        ParametroFactor::insert([
            ['version_id' => 1, 'factor_id' => 1, 'peso' => 20],
            ['version_id' => 1, 'factor_id' => 2, 'peso' => 15],
            ['version_id' => 1, 'factor_id' => 3, 'peso' => 10],
            ['version_id' => 1, 'factor_id' => 4, 'peso' => 10],
            ['version_id' => 1, 'factor_id' => 5, 'peso' => 20],
            ['version_id' => 1, 'factor_id' => 6, 'peso' => 15],
            ['version_id' => 1, 'factor_id' => 7, 'peso' => 10],
        ]);
    }
}
