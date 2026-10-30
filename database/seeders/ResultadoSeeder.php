<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultadoSeeder extends Seeder
{
    /**
     * Calcula con el procedimiento sp_calcular_resultado los eventos EV-001 a EV-018 (usuario 2 = analista1).
     * EV-019 y EV-020 se dejan sin calcular a propósito. Los resultados quedan con los ids 1 a 18 y el recálculo de EV-009 con el 19.
     */
    public function run(): void
    {
        // EV-001 (base)
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [1, 2]);
        // EV-002 (base)
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [2, 2]);
        // EV-003 (base)
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [3, 2]);
        // EV-004 (base)
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [4, 2]);
        // EV-005
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [5, 2]);
        // EV-006
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [6, 2]);
        // EV-007
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [7, 2]);
        // EV-008
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [8, 2]);
        // EV-009
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [9, 2]);
        // EV-010
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [10, 2]);
        // EV-011
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [11, 2]);
        // EV-012
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [12, 2]);
        // EV-013
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [13, 2]);
        // EV-014
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [14, 2]);
        // EV-015
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [15, 2]);
        // EV-016
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [16, 2]);
        // EV-017
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [17, 2]);
        // EV-018
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [18, 2]);
        // EV-009 otra vez: el primer resultado queda como reemplazado
        DB::statement('CALL sp_calcular_resultado(?, ?, @resultado)', [9, 2]);
    }
}
 