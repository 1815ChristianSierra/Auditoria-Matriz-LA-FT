<?php

namespace Database\Seeders;

use App\Models\CoincidenciaLista;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoincidenciaListaSeeder extends Seeder
{
    /**
     * Cruces entre clientes y listas: cuatro por verificar (parcial), tres confirmadas y cuatro descartadas como falsos positivos. Usuarios: 2 = analista1, 3 = supervisor1.
     */
    public function run(): void
    {
        CoincidenciaLista::insert([
            // Coincidencia confirmada de EV-004 (base)
            ['cliente_id' => 4, 'lista_id' => 1, 'tipo_coincidencia' => 'confirmada', 'verificada_por' => 2, 'observacion' => 'Coincidencia confirmada por el analista (dato sintético)'],
            // Resto de los cruces
            ['cliente_id' => 5, 'lista_id' => 2, 'tipo_coincidencia' => 'confirmada', 'verificada_por' => 3, 'observacion' => 'Coincidencia confirmada: es el mismo funcionario, cargo vigente (dato sintético)'],
            ['cliente_id' => 7, 'lista_id' => 7, 'tipo_coincidencia' => 'confirmada', 'verificada_por' => 3, 'observacion' => 'Coincidencia confirmada: misma razón social y mismo representante (dato sintético)'],
            ['cliente_id' => 12, 'lista_id' => 3, 'tipo_coincidencia' => 'parcial', 'verificada_por' => 2, 'observacion' => 'Nombre muy parecido; falta confirmar la identidad con documentos (dato sintético)'],
            ['cliente_id' => 9, 'lista_id' => 9, 'tipo_coincidencia' => 'parcial', 'verificada_por' => 2, 'observacion' => 'La razón social coincide en parte; pendiente de comparar a los socios (dato sintético)'],
            ['cliente_id' => 13, 'lista_id' => 8, 'tipo_coincidencia' => 'parcial', 'verificada_por' => 2, 'observacion' => 'Nombre comercial parecido; pendiente de verificar la dirección (dato sintético)'],
            ['cliente_id' => 15, 'lista_id' => 11, 'tipo_coincidencia' => 'parcial', 'verificada_por' => 2, 'observacion' => 'Apellido casi igual; pendiente de validar fecha de nacimiento (dato sintético)'],
            ['cliente_id' => 8, 'lista_id' => 4, 'tipo_coincidencia' => 'descartada', 'verificada_por' => 3, 'observacion' => 'Homónimo: distinta fecha de nacimiento y otra actividad; no es la misma persona (dato sintético)'],
            ['cliente_id' => 10, 'lista_id' => 6, 'tipo_coincidencia' => 'descartada', 'verificada_por' => 3, 'observacion' => 'Apellido distinto en el documento de identidad; falso positivo (dato sintético)'],
            ['cliente_id' => 14, 'lista_id' => 10, 'tipo_coincidencia' => 'descartada', 'verificada_por' => 3, 'observacion' => 'Otra sociedad con nombre parecido: distinto registro y distintos socios (dato sintético)'],
            ['cliente_id' => 16, 'lista_id' => 12, 'tipo_coincidencia' => 'descartada', 'verificada_por' => 2, 'observacion' => 'Entidades distintas: otra actividad y otra jurisdicción (dato sintético)'],
        ]);
    }
}
