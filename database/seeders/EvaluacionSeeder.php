<?php

namespace Database\Seeders;

use App\Models\Evaluacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EvaluacionSeeder extends Seeder
{
    /**
     * Tres evaluaciones con la versión v1.0 (aprobada): EVAL-2026-01 (la del script base), una cerrada y una abierta.
     */
    public function run(): void
    {
        Evaluacion::insert([
            ['id' => 1, 'codigo' => 'EVAL-2026-01', 'periodo_inicio' => '2026-09-01', 'periodo_fin' => '2026-09-30', 'alcance' => 'Prueba del prototipo con cuatro escenarios sintéticos (EV-001 a EV-004)', 'responsable_id' => 2, 'version_id' => 1, 'estado' => 'abierta'],
            ['id' => 2, 'codigo' => 'EVAL-2025-12', 'periodo_inicio' => '2025-12-01', 'periodo_fin' => '2025-12-31', 'alcance' => 'Cierre de año 2025: operaciones internacionales y de efectivo (evaluación ya cerrada, solo lectura)', 'responsable_id' => 3, 'version_id' => 1, 'estado' => 'cerrada'],
            ['id' => 3, 'codigo' => 'EVAL-2026-02', 'periodo_inicio' => '2026-10-01', 'periodo_fin' => '2026-10-31', 'alcance' => 'Segunda evaluación del prototipo: clientes nuevos y operaciones de octubre de 2026', 'responsable_id' => 2, 'version_id' => 1, 'estado' => 'abierta'],
        ]);
    }
}
