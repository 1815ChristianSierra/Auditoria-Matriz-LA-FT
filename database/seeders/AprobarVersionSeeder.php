<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VersionParametros;

class AprobarVersionSeeder extends Seeder
{
    /**
     * Flujo de aprobación de v1.0: borrador -> propuesta -> aprobada.
     * Aprueba supervisor1 (usuario 3), distinto de quien propone (administrador1, usuario 1): en la aplicación solo el rol supervisor decide las versiones.
     * Los triggers de la BD comprueban que los pesos sumen 100 y dejan APROBAR_VERSION en la bitácora.
     */
    public function run(): void
    {
        VersionParametros::where('id', 1)->update(['estado' => 'propuesta']);

        VersionParametros::where('id', 1)->update([
            'estado' => 'aprobada',
            'aprobador_id' => 3,
            'motivo_decision' => 'Pesos suman 100 % y coinciden con el diccionario de factores',
        ]);
    }
}
