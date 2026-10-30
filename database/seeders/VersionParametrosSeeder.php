<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VersionParametros;

class VersionParametrosSeeder extends Seeder
{
    /**
     * Versión v1.0 en borrador, propuesta por administrador1 (usuario 1). AprobarVersionSeeder la aprueba al final, cuando ya tiene sus parámetros.
     */
    public function run(): void
    {
        VersionParametros::insert([
            ['id' => 1, 'numero' => 'v1.0', 'puntaje_dato_faltante' => 3, 'motivo' => 'Pesos iniciales del diccionario de factores', 'proponente_id' => 1],
        ]);
    }
}
