<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParametroAgravante;

class ParametroAgravanteSeeder extends Seeder
{
    /**
     * Cada agravante suma 2 puntos en v1.0 (con el tope de la versión).
     */
    public function run(): void
    {
        ParametroAgravante::insert([
            ['version_id' => 1, 'agravante_id' => 1, 'puntos' => 2],
            ['version_id' => 1, 'agravante_id' => 2, 'puntos' => 2],
            ['version_id' => 1, 'agravante_id' => 3, 'puntos' => 2],
            ['version_id' => 1, 'agravante_id' => 4, 'puntos' => 2],
            ['version_id' => 1, 'agravante_id' => 5, 'puntos' => 2],
        ]);
    }
}
