<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Agravante;

class AgravanteSeeder extends Seeder
{
    /**
     * Catálogo de agravantes (ids 1 a 5).
     */
    public function run(): void
    {
        Agravante::insert([
            ['id' => 1, 'codigo' => 'AGR-01', 'nombre' => 'Coincidencia parcial en listas de sanciones'],
            ['id' => 2, 'codigo' => 'AGR-02', 'nombre' => 'Uso intensivo de efectivo'],
            ['id' => 3, 'codigo' => 'AGR-03', 'nombre' => 'Alertas o reportes previos del cliente'],
            ['id' => 4, 'codigo' => 'AGR-04', 'nombre' => 'Noticias adversas'],
            ['id' => 5, 'codigo' => 'AGR-05', 'nombre' => 'Información incongruente'],
        ]);
    }
}
 