<?php

namespace Database\Seeders;

use App\Models\ListaRestrictiva;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ListaRestrictivaSeeder extends Seeder
{
    /**
     * Registros de las listas sintéticas de PEP y de sanciones (el 1 es el de EV-004; el 13 está inactivo; el 14 y el 15 no coinciden con ningún cliente).
     */
    public function run(): void
    {
        ListaRestrictiva::insert([
            // Registro de la lista de sanciones de EV-004 (base)
            ['id' => 1, 'nombre_ficticio' => 'Importadora Ficticia D', 'tipo_lista' => 'SANCIONES', 'fuente_ficticia' => 'Lista sintética de sanciones (ficticia)', 'activo' => 1, 'sintetico' => 1],
            // Registros de PEP y de sanciones para probar coincidencias
            ['id' => 2, 'nombre_ficticio' => 'Ricardo Ficticio Alvarado', 'tipo_lista' => 'PEP', 'fuente_ficticia' => 'Registro sintético de PEP (ficticio)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 3, 'nombre_ficticio' => 'Lucia Ficticia Barrios de Mendez', 'tipo_lista' => 'PEP', 'fuente_ficticia' => 'Registro sintético de PEP (ficticio)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 4, 'nombre_ficticio' => 'Marta Ficticia Cifuentes', 'tipo_lista' => 'PEP', 'fuente_ficticia' => 'Registro sintético de PEP (ficticio)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 5, 'nombre_ficticio' => 'Alcalde Ficticio Rojas', 'tipo_lista' => 'PEP', 'fuente_ficticia' => 'Registro sintético de PEP (ficticio)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 6, 'nombre_ficticio' => 'Pedro Ficticio Lemos', 'tipo_lista' => 'PEP', 'fuente_ficticia' => 'Registro sintético de PEP (ficticio)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 7, 'nombre_ficticio' => 'Remesadora Ficticia F S.A.', 'tipo_lista' => 'SANCIONES', 'fuente_ficticia' => 'Lista sintética de sanciones (ficticia)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 8, 'nombre_ficticio' => 'Casa de Cambios Ficticia Gamma', 'tipo_lista' => 'SANCIONES', 'fuente_ficticia' => 'Lista sintética de sanciones (ficticia)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 9, 'nombre_ficticio' => 'Inversiones Ficticias Delta Holding', 'tipo_lista' => 'SANCIONES', 'fuente_ficticia' => 'Lista sintética regional de sanciones (ficticia)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 10, 'nombre_ficticio' => 'Transportes Ficticios Hache', 'tipo_lista' => 'SANCIONES', 'fuente_ficticia' => 'Lista sintética regional de sanciones (ficticia)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 11, 'nombre_ficticio' => 'Andres Ficticio Quiroga', 'tipo_lista' => 'SANCIONES', 'fuente_ficticia' => 'Lista sintética de sanciones (ficticia)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 12, 'nombre_ficticio' => 'Fundacion Esperanza del Norte', 'tipo_lista' => 'SANCIONES', 'fuente_ficticia' => 'Lista sintética de sanciones (ficticia)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 13, 'nombre_ficticio' => 'Red Ficticia del Norte', 'tipo_lista' => 'SANCIONES', 'fuente_ficticia' => 'Lista sintética de sanciones (ficticia)', 'activo' => 0, 'sintetico' => 1],
            ['id' => 14, 'nombre_ficticio' => 'Funcionario Ficticio Zeta', 'tipo_lista' => 'PEP', 'fuente_ficticia' => 'Registro sintético de PEP (ficticio)', 'activo' => 1, 'sintetico' => 1],
            ['id' => 15, 'nombre_ficticio' => 'Comercializadora Ficticia Sigma', 'tipo_lista' => 'SANCIONES', 'fuente_ficticia' => 'Lista sintética de sanciones (ficticia)', 'activo' => 1, 'sintetico' => 1],
        ]);
    }
}
