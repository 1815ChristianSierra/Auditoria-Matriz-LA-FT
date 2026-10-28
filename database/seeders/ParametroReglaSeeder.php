<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParametroRegla;

class ParametroReglaSeeder extends Seeder
{
    /**
     * Regla de escalamiento ESC-01 de v1.0: PEP/Sanciones en nivel 5 fuerza la banda Crítico.
     */
    public function run(): void
    {
        ParametroRegla::insert([
            ['version_id' => 1, 'codigo' => 'ESC-01', 'descripcion' => 'Coincidencia confirmada en lista de sanciones (PEP/Sanciones = 5): clasifica como Crítico', 'factor_id' => 7, 'nivel_minimo' => 5, 'banda_forzada' => 'Crítico'],
        ]);
    }
}
