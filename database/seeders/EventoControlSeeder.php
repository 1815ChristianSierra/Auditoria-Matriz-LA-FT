<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EventoControl;

class EventoControlSeeder extends Seeder
{
    /**
     * Controles marcados en los eventos, cada uno con su evidencia (1 = CTL-01, 2 = CTL-02).
     */
    public function run(): void
    {
        EventoControl::insert([
            ['evento_id' => 5, 'control_id' => 1, 'evidencia' => 'Carta de crédito y contrato de exportación archivados', 'registrado_por' => 2],
            ['evento_id' => 9, 'control_id' => 2, 'evidencia' => 'Debida diligencia intensificada realizada hace cinco meses', 'registrado_por' => 2],
            ['evento_id' => 12, 'control_id' => 2, 'evidencia' => 'Debida diligencia intensificada vigente (menos de 12 meses)', 'registrado_por' => 2],
            ['evento_id' => 13, 'control_id' => 1, 'evidencia' => 'Soporte documental del origen de fondos verificado', 'registrado_por' => 2],
            ['evento_id' => 14, 'control_id' => 1, 'evidencia' => 'Facturas y contrato del proveedor archivados', 'registrado_por' => 2],
            ['evento_id' => 16, 'control_id' => 1, 'evidencia' => 'Contratos de consultoría y facturas verificados', 'registrado_por' => 2],
            ['evento_id' => 17, 'control_id' => 1, 'evidencia' => 'Factura del proveedor y declaración aduanera archivadas', 'registrado_por' => 2],
        ]);
    }
}
 