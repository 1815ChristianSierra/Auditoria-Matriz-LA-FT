<?php

namespace Database\Seeders;

use App\Models\EventoAgravante;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventoAgravanteSeeder extends Seeder
{
    /**
     * Agravantes marcados en los eventos, cada uno con su evidencia (1 = AGR-01 ... 5 = AGR-05).
     */
    public function run(): void
    {
        EventoAgravante::insert([
            ['evento_id' => 6, 'agravante_id' => 2, 'evidencia' => 'Tres depósitos en efectivo en tres sucursales en una semana', 'registrado_por' => 2],
            ['evento_id' => 7, 'agravante_id' => 5, 'evidencia' => 'El origen declarado no coincide con la documentación entregada', 'registrado_por' => 2],
            ['evento_id' => 10, 'agravante_id' => 3, 'evidencia' => 'Reporte interno previo por fraccionamiento de remesas', 'registrado_por' => 2],
            ['evento_id' => 11, 'agravante_id' => 4, 'evidencia' => 'Nota de prensa sintética sobre el familiar del cliente', 'registrado_por' => 2],
            ['evento_id' => 12, 'agravante_id' => 4, 'evidencia' => 'Noticias adversas sobre el beneficiario final', 'registrado_por' => 2],
            ['evento_id' => 12, 'agravante_id' => 5, 'evidencia' => 'Los socios declarados no coinciden con el registro', 'registrado_por' => 2],
            ['evento_id' => 13, 'agravante_id' => 1, 'evidencia' => 'Coincidencia parcial en lista de sanciones', 'registrado_por' => 2],
            ['evento_id' => 13, 'agravante_id' => 2, 'evidencia' => 'Uso intensivo de efectivo en la semana', 'registrado_por' => 2],
            ['evento_id' => 16, 'agravante_id' => 1, 'evidencia' => 'Apellido casi igual a un registro de sanciones', 'registrado_por' => 2],
        ]);
    }
}
