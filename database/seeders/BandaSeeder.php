<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banda;

class BandaSeeder extends Seeder
{
    /**
     * Bandas de v1.0: Bajo 0-24, Medio 25-49, Alto 50-74, Crítico 75-100.
     */
    public function run(): void
    {
        Banda::insert([
            ['version_id' => 1, 'nombre' => 'Bajo', 'orden' => 1, 'desde' => 0, 'hasta' => 24, 'tratamiento' => 'Monitoreo normal.'],
            ['version_id' => 1, 'nombre' => 'Medio', 'orden' => 2, 'desde' => 25, 'hasta' => 49, 'tratamiento' => 'Revisión en cola estándar.'],
            ['version_id' => 1, 'nombre' => 'Alto', 'orden' => 3, 'desde' => 50, 'hasta' => 74, 'tratamiento' => 'Análisis prioritario con debida diligencia intensificada.'],
            ['version_id' => 1, 'nombre' => 'Crítico', 'orden' => 4, 'desde' => 75, 'hasta' => 100, 'tratamiento' => 'Atención inmediata del analista.'],
        ]);
    }
}
