<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParametroControl;

class ParametroControlSeeder extends Seeder
{
    /**
     * CTL-01 resta 2 puntos y CTL-02 resta 1 punto en v1.0 (con el tope de la versión).
     */
    public function run(): void
    {
        ParametroControl::insert([
            ['version_id' => 1, 'control_id' => 1, 'puntos_resta' => 2],
            ['version_id' => 1, 'control_id' => 2, 'puntos_resta' => 1],
        ]);
    }
}
