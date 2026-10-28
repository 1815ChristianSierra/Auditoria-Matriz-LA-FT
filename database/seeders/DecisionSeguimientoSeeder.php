<?php

namespace Database\Seeders;

use App\Models\DecisionSeguimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DecisionSeguimientoSeeder extends Seeder
{
    /**
     * Decisiones humanas sobre los resultados vigentes: en revisión, escalar, descartar y plan de acción (con responsable, fecha y estado).
     * Todas las decisiones las registra supervisor1 (usuario 3): en la aplicación solo el rol supervisor puede decidir. Va después de ResultadoSeeder porque usa los ids de los resultados.
     */
    public function run(): void
    {
        DecisionSeguimiento::insert([
            // Evaluación EVAL-2026-01 (SQL base)
            ['resultado_id' => 3, 'decision' => 'en_revision', 'justificacion' => 'Se solicita al cliente aclarar el origen de las transferencias acumuladas.', 'decidido_por' => 3, 'responsable_plan' => null, 'plan_accion' => null, 'fecha_compromiso' => null, 'estado_seguimiento' => null, 'evidencia' => null],
            ['resultado_id' => 4, 'decision' => 'escalar_a_cumplimiento', 'justificacion' => 'Coincidencia confirmada en lista de sanciones: se remite a la Oficialía de Cumplimiento.', 'decidido_por' => 3, 'responsable_plan' => null, 'plan_accion' => null, 'fecha_compromiso' => null, 'estado_seguimiento' => null, 'evidencia' => null],
            // Evaluación EVAL-2025-12 (cerrada)
            ['resultado_id' => 5, 'decision' => 'descartar', 'justificacion' => 'Carta de crédito respaldada por contrato y exportador con relación de siete años.', 'decidido_por' => 3, 'responsable_plan' => null, 'plan_accion' => null, 'fecha_compromiso' => null, 'estado_seguimiento' => null, 'evidencia' => null],
            ['resultado_id' => 6, 'decision' => 'plan_de_accion', 'justificacion' => 'El fraccionamiento no tiene justificación comercial; se pide soporte y se monitorea el cliente.', 'decidido_por' => 3, 'responsable_plan' => 2, 'plan_accion' => 'Solicitar la justificación de los tres depósitos y revisar el perfil transaccional en 15 días.', 'fecha_compromiso' => '2026-01-15', 'estado_seguimiento' => 'cerrado', 'evidencia' => 'REF-2025-114'],
            ['resultado_id' => 7, 'decision' => 'escalar_a_cumplimiento', 'justificacion' => 'Donación del exterior sin documentación del origen; se remite a la Oficialía.', 'decidido_por' => 3, 'responsable_plan' => null, 'plan_accion' => null, 'fecha_compromiso' => null, 'estado_seguimiento' => null, 'evidencia' => null],
            ['resultado_id' => 8, 'decision' => 'descartar', 'justificacion' => 'Pago habitual de matrícula, sin ninguna señal de riesgo.', 'decidido_por' => 3, 'responsable_plan' => null, 'plan_accion' => null, 'fecha_compromiso' => null, 'estado_seguimiento' => null, 'evidencia' => null],
            // Evaluación EVAL-2026-02 (abierta)
            ['resultado_id' => 19, 'decision' => 'en_revision', 'justificacion' => 'Se pide a Cumplimiento validar el cargo vigente del cliente antes de decidir.', 'decidido_por' => 3, 'responsable_plan' => null, 'plan_accion' => null, 'fecha_compromiso' => null, 'estado_seguimiento' => null, 'evidencia' => null],
            ['resultado_id' => 10, 'decision' => 'escalar_a_cumplimiento', 'justificacion' => 'La regla ESC-01 aplica por coincidencia confirmada en sanciones; se remite a la Oficialía.', 'decidido_por' => 3, 'responsable_plan' => null, 'plan_accion' => null, 'fecha_compromiso' => null, 'estado_seguimiento' => null, 'evidencia' => null],
            ['resultado_id' => 12, 'decision' => 'en_revision', 'justificacion' => 'Se solicitan los documentos de la sociedad y el beneficiario final.', 'decidido_por' => 3, 'responsable_plan' => null, 'plan_accion' => null, 'fecha_compromiso' => null, 'estado_seguimiento' => null, 'evidencia' => null],
            ['resultado_id' => 12, 'decision' => 'plan_de_accion', 'justificacion' => 'Operación aceptada con debida diligencia intensificada y seguimiento mensual.', 'decidido_por' => 3, 'responsable_plan' => 2, 'plan_accion' => 'Solicitar los documentos de la sociedad y actualizar la debida diligencia intensificada.', 'fecha_compromiso' => '2026-11-20', 'estado_seguimiento' => 'en_curso', 'evidencia' => 'REF-2026-031'],
            ['resultado_id' => 14, 'decision' => 'descartar', 'justificacion' => 'Pago recurrente a proveedor habitual, con facturas archivadas.', 'decidido_por' => 3, 'responsable_plan' => null, 'plan_accion' => null, 'fecha_compromiso' => null, 'estado_seguimiento' => null, 'evidencia' => null],
        ]);
    }
}
