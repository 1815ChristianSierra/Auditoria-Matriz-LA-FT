<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Control;

class ControlSeeder extends Seeder
{
    /**
     * Catálogo de controles compensatorios (ids 1 y 2).
     */
    public function run(): void
    {
        Control::insert([
            ['id' => 1, 'codigo' => 'CTL-01', 'nombre' => 'Soporte documental verificado del origen de fondos', 'tipo' => 'preventivo'],
            ['id' => 2, 'codigo' => 'CTL-02', 'nombre' => 'Debida diligencia intensificada vigente (menos de 12 meses)', 'tipo' => 'preventivo'],
        ]);
    }
}
 