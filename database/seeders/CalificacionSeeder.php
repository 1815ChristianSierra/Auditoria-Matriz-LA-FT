<?php

namespace Database\Seeders;

use App\Models\Calificacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CalificacionSeeder extends Seeder
{

    /**
     * Siete factores por evento (nivel 0 a 5 con su justificación). EV-018 solo trae tres factores (el resto lo completa el cálculo con dato faltante) y EV-019 solo dos; EV-020 no trae ninguno.
     */
    public function run(): void
    {
        Calificacion::insert([
            // EV-001 a EV-004 (base)
            ['evento_id' => 1, 'factor_id' => 1, 'nivel' => 1, 'justificacion' => '$750, dentro del comportamiento habitual', 'calificado_por' => 2],
            ['evento_id' => 1, 'factor_id' => 2, 'nivel' => 0, 'justificacion' => 'Comportamiento habitual del cliente', 'calificado_por' => 2],
            ['evento_id' => 1, 'factor_id' => 3, 'nivel' => 1, 'justificacion' => 'Cuenta de nómina', 'calificado_por' => 2],
            ['evento_id' => 1, 'factor_id' => 4, 'nivel' => 1, 'justificacion' => 'Agencia presencial con verificación de identidad', 'calificado_por' => 2],
            ['evento_id' => 1, 'factor_id' => 5, 'nivel' => 1, 'justificacion' => 'Operación local en zona de bajo riesgo', 'calificado_por' => 2],
            ['evento_id' => 1, 'factor_id' => 6, 'nivel' => 1, 'justificacion' => 'Asalariado, más de 5 años de relación, ingresos verificados', 'calificado_por' => 2],
            ['evento_id' => 1, 'factor_id' => 7, 'nivel' => 0, 'justificacion' => 'Sin relación con PEP, sin coincidencia', 'calificado_por' => 2],
            ['evento_id' => 2, 'factor_id' => 1, 'nivel' => 3, 'justificacion' => '$6,500', 'calificado_por' => 2],
            ['evento_id' => 2, 'factor_id' => 2, 'nivel' => 3, 'justificacion' => 'El doble de operaciones que lo habitual', 'calificado_por' => 2],
            ['evento_id' => 2, 'factor_id' => 3, 'nivel' => 3, 'justificacion' => 'Cuenta monetaria empresarial', 'calificado_por' => 2],
            ['evento_id' => 2, 'factor_id' => 4, 'nivel' => 2, 'justificacion' => 'Banca en línea, cliente previamente verificado', 'calificado_por' => 2],
            ['evento_id' => 2, 'factor_id' => 5, 'nivel' => 2, 'justificacion' => 'País con marco LA/FT sólido', 'calificado_por' => 2],
            ['evento_id' => 2, 'factor_id' => 6, 'nivel' => 2, 'justificacion' => 'Persona individual, negocio formal y documentado', 'calificado_por' => 2],
            ['evento_id' => 2, 'factor_id' => 7, 'nivel' => 0, 'justificacion' => 'Sin relación con PEP, sin coincidencia', 'calificado_por' => 2],
            ['evento_id' => 3, 'factor_id' => 1, 'nivel' => 4, 'justificacion' => '$35,000 acumulados en el periodo', 'calificado_por' => 2],
            ['evento_id' => 3, 'factor_id' => 2, 'nivel' => 4, 'justificacion' => 'Varias operaciones cercanas al umbral', 'calificado_por' => 2],
            ['evento_id' => 3, 'factor_id' => 3, 'nivel' => 4, 'justificacion' => 'Transferencias internacionales', 'calificado_por' => 2],
            ['evento_id' => 3, 'factor_id' => 4, 'nivel' => 4, 'justificacion' => 'Vinculación completamente digital', 'calificado_por' => 2],
            ['evento_id' => 3, 'factor_id' => 5, 'nivel' => 4, 'justificacion' => 'Jurisdicción bajo monitoreo intensificado', 'calificado_por' => 2],
            ['evento_id' => 3, 'factor_id' => 6, 'nivel' => 4, 'justificacion' => 'Persona jurídica con estructura compleja', 'calificado_por' => 2],
            ['evento_id' => 3, 'factor_id' => 7, 'nivel' => 0, 'justificacion' => 'Sin relación con PEP, sin coincidencia', 'calificado_por' => 2],
            ['evento_id' => 4, 'factor_id' => 1, 'nivel' => 5, 'justificacion' => '$65,000', 'calificado_por' => 2],
            ['evento_id' => 4, 'factor_id' => 2, 'nivel' => 5, 'justificacion' => 'Gran cantidad de operaciones en poco tiempo', 'calificado_por' => 2],
            ['evento_id' => 4, 'factor_id' => 3, 'nivel' => 4, 'justificacion' => 'Transferencias internacionales', 'calificado_por' => 2],
            ['evento_id' => 4, 'factor_id' => 4, 'nivel' => 4, 'justificacion' => 'Vinculación completamente digital', 'calificado_por' => 2],
            ['evento_id' => 4, 'factor_id' => 5, 'nivel' => 5, 'justificacion' => 'Jurisdicción de mayor riesgo', 'calificado_por' => 2],
            ['evento_id' => 4, 'factor_id' => 6, 'nivel' => 5, 'justificacion' => 'Actividad de alto riesgo', 'calificado_por' => 2],
            ['evento_id' => 4, 'factor_id' => 7, 'nivel' => 5, 'justificacion' => 'Coincidencia confirmada en lista de sanciones: escalamiento', 'calificado_por' => 2],
            // Eventos de las evaluaciones nuevas
            // EV-005
            ['evento_id' => 5, 'factor_id' => 1, 'nivel' => 2, 'justificacion' => '$42,000 respaldados por el contrato de exportación', 'calificado_por' => 2],
            ['evento_id' => 5, 'factor_id' => 2, 'nivel' => 1, 'justificacion' => 'Operación puntual dentro del calendario de cosecha', 'calificado_por' => 2],
            ['evento_id' => 5, 'factor_id' => 3, 'nivel' => 3, 'justificacion' => 'Carta de crédito: producto documentado', 'calificado_por' => 2],
            ['evento_id' => 5, 'factor_id' => 4, 'nivel' => 1, 'justificacion' => 'Presencial y verificado', 'calificado_por' => 2],
            ['evento_id' => 5, 'factor_id' => 5, 'nivel' => 2, 'justificacion' => 'País cooperante con marco LA/FT sólido', 'calificado_por' => 2],
            ['evento_id' => 5, 'factor_id' => 6, 'nivel' => 1, 'justificacion' => 'Exportador con más de 7 años de relación', 'calificado_por' => 2],
            ['evento_id' => 5, 'factor_id' => 7, 'nivel' => 0, 'justificacion' => 'Sin vínculo con PEP ni coincidencias', 'calificado_por' => 2],
            // EV-006
            ['evento_id' => 6, 'factor_id' => 1, 'nivel' => 4, 'justificacion' => '$9,800: justo bajo el umbral de reporte', 'calificado_por' => 2],
            ['evento_id' => 6, 'factor_id' => 2, 'nivel' => 4, 'justificacion' => 'Tres depósitos en una semana', 'calificado_por' => 2],
            ['evento_id' => 6, 'factor_id' => 3, 'nivel' => 4, 'justificacion' => 'Efectivo fraccionado, producto de alto riesgo', 'calificado_por' => 2],
            ['evento_id' => 6, 'factor_id' => 4, 'nivel' => 1, 'justificacion' => 'Presencial, con identidad verificada', 'calificado_por' => 2],
            ['evento_id' => 6, 'factor_id' => 5, 'nivel' => 2, 'justificacion' => 'Operación local en tres sucursales', 'calificado_por' => 2],
            ['evento_id' => 6, 'factor_id' => 6, 'nivel' => 3, 'justificacion' => 'Volumen de efectivo poco coherente con el giro de transporte', 'calificado_por' => 2],
            ['evento_id' => 6, 'factor_id' => 7, 'nivel' => 0, 'justificacion' => 'Sin vínculo con PEP ni coincidencias', 'calificado_por' => 2],
            // EV-007
            ['evento_id' => 7, 'factor_id' => 1, 'nivel' => 3, 'justificacion' => '$15,000 recibidos como donación', 'calificado_por' => 2],
            ['evento_id' => 7, 'factor_id' => 2, 'nivel' => 2, 'justificacion' => 'Dos transferencias en diez días', 'calificado_por' => 2],
            ['evento_id' => 7, 'factor_id' => 3, 'nivel' => 4, 'justificacion' => 'Transferencia internacional recibida', 'calificado_por' => 2],
            ['evento_id' => 7, 'factor_id' => 4, 'nivel' => 4, 'justificacion' => 'Intermediario externo, sin verificación directa', 'calificado_por' => 2],
            ['evento_id' => 7, 'factor_id' => 5, 'nivel' => 5, 'justificacion' => 'Origen en jurisdicción de alto riesgo', 'calificado_por' => 2],
            ['evento_id' => 7, 'factor_id' => 6, 'nivel' => 4, 'justificacion' => 'Donación sin soporte en una fundación', 'calificado_por' => 2],
            ['evento_id' => 7, 'factor_id' => 7, 'nivel' => 1, 'justificacion' => 'Sin vínculo confirmado con PEP', 'calificado_por' => 2],
            // EV-008
            ['evento_id' => 8, 'factor_id' => 1, 'nivel' => 0, 'justificacion' => 'Monto habitual para el perfil', 'calificado_por' => 2],
            ['evento_id' => 8, 'factor_id' => 2, 'nivel' => 0, 'justificacion' => 'Pago semestral habitual', 'calificado_por' => 2],
            ['evento_id' => 8, 'factor_id' => 3, 'nivel' => 1, 'justificacion' => 'Cuenta de ahorro', 'calificado_por' => 2],
            ['evento_id' => 8, 'factor_id' => 4, 'nivel' => 1, 'justificacion' => 'Banca en línea con cliente verificado', 'calificado_por' => 2],
            ['evento_id' => 8, 'factor_id' => 5, 'nivel' => 0, 'justificacion' => 'Operación local', 'calificado_por' => 2],
            ['evento_id' => 8, 'factor_id' => 6, 'nivel' => 1, 'justificacion' => 'Estudiante con ingresos limitados', 'calificado_por' => 2],
            ['evento_id' => 8, 'factor_id' => 7, 'nivel' => 0, 'justificacion' => 'Sin vínculo con PEP ni coincidencias', 'calificado_por' => 2],
            // EV-009
            ['evento_id' => 9, 'factor_id' => 1, 'nivel' => 5, 'justificacion' => '$80,000: varias veces el perfil', 'calificado_por' => 2],
            ['evento_id' => 9, 'factor_id' => 2, 'nivel' => 4, 'justificacion' => 'Cuatro transferencias en dos semanas', 'calificado_por' => 2],
            ['evento_id' => 9, 'factor_id' => 3, 'nivel' => 5, 'justificacion' => 'Transferencias internacionales de alto riesgo', 'calificado_por' => 2],
            ['evento_id' => 9, 'factor_id' => 4, 'nivel' => 4, 'justificacion' => 'Vinculación digital', 'calificado_por' => 2],
            ['evento_id' => 9, 'factor_id' => 5, 'nivel' => 5, 'justificacion' => 'Destino en jurisdicción de alto riesgo', 'calificado_por' => 2],
            ['evento_id' => 9, 'factor_id' => 6, 'nivel' => 4, 'justificacion' => 'Operación incoherente con los ingresos de un funcionario', 'calificado_por' => 2],
            ['evento_id' => 9, 'factor_id' => 7, 'nivel' => 5, 'justificacion' => 'Coincidencia confirmada: vínculo PEP vigente', 'calificado_por' => 2],
            // EV-010
            ['evento_id' => 10, 'factor_id' => 1, 'nivel' => 3, 'justificacion' => '$27,500 acumulados en remesas', 'calificado_por' => 2],
            ['evento_id' => 10, 'factor_id' => 2, 'nivel' => 4, 'justificacion' => 'Más de veinte remesas en diez días', 'calificado_por' => 2],
            ['evento_id' => 10, 'factor_id' => 3, 'nivel' => 3, 'justificacion' => 'Remesas fraccionadas', 'calificado_por' => 2],
            ['evento_id' => 10, 'factor_id' => 4, 'nivel' => 3, 'justificacion' => 'Intermediario externo', 'calificado_por' => 2],
            ['evento_id' => 10, 'factor_id' => 5, 'nivel' => 4, 'justificacion' => 'Destinos bajo monitoreo intensificado', 'calificado_por' => 2],
            ['evento_id' => 10, 'factor_id' => 6, 'nivel' => 3, 'justificacion' => 'Remesadora de reciente relación', 'calificado_por' => 2],
            ['evento_id' => 10, 'factor_id' => 7, 'nivel' => 5, 'justificacion' => 'Coincidencia confirmada en lista de sanciones', 'calificado_por' => 2],
            // EV-011
            ['evento_id' => 11, 'factor_id' => 1, 'nivel' => 3, 'justificacion' => '$18,000: varias veces su depósito habitual', 'calificado_por' => 2],
            ['evento_id' => 11, 'factor_id' => 2, 'nivel' => 3, 'justificacion' => 'Primer depósito de este tipo en el año', 'calificado_por' => 2],
            ['evento_id' => 11, 'factor_id' => 3, 'nivel' => 4, 'justificacion' => 'Efectivo concentrado', 'calificado_por' => 2],
            ['evento_id' => 11, 'factor_id' => 4, 'nivel' => 2, 'justificacion' => 'Presencial con verificación', 'calificado_por' => 2],
            ['evento_id' => 11, 'factor_id' => 5, 'nivel' => 1, 'justificacion' => 'Operación local', 'calificado_por' => 2],
            ['evento_id' => 11, 'factor_id' => 6, 'nivel' => 3, 'justificacion' => 'Actividad poco coherente con el monto', 'calificado_por' => 2],
            ['evento_id' => 11, 'factor_id' => 7, 'nivel' => 3, 'justificacion' => 'Vínculo familiar con PEP, coincidencia parcial sin verificar', 'calificado_por' => 2],
            // EV-012
            ['evento_id' => 12, 'factor_id' => 1, 'nivel' => 5, 'justificacion' => '$250,000: varias veces el perfil de la sociedad', 'calificado_por' => 2],
            ['evento_id' => 12, 'factor_id' => 2, 'nivel' => 2, 'justificacion' => 'Dos operaciones en el mes', 'calificado_por' => 2],
            ['evento_id' => 12, 'factor_id' => 3, 'nivel' => 5, 'justificacion' => 'Compra de inmuebles con fondos del exterior', 'calificado_por' => 2],
            ['evento_id' => 12, 'factor_id' => 4, 'nivel' => 3, 'justificacion' => 'Vinculación digital con documentos completos', 'calificado_por' => 2],
            ['evento_id' => 12, 'factor_id' => 5, 'nivel' => 5, 'justificacion' => 'Fondos desde zona offshore', 'calificado_por' => 2],
            ['evento_id' => 12, 'factor_id' => 6, 'nivel' => 5, 'justificacion' => 'Estructura sin actividad económica comprobable', 'calificado_por' => 2],
            ['evento_id' => 12, 'factor_id' => 7, 'nivel' => 2, 'justificacion' => 'Sin coincidencia, noticias adversas en revisión', 'calificado_por' => 2],
            // EV-013
            ['evento_id' => 13, 'factor_id' => 1, 'nivel' => 3, 'justificacion' => '$9,500 en operaciones cercanas al umbral', 'calificado_por' => 2],
            ['evento_id' => 13, 'factor_id' => 2, 'nivel' => 4, 'justificacion' => 'Varias operaciones diarias', 'calificado_por' => 2],
            ['evento_id' => 13, 'factor_id' => 3, 'nivel' => 3, 'justificacion' => 'Compra y venta de divisas', 'calificado_por' => 2],
            ['evento_id' => 13, 'factor_id' => 4, 'nivel' => 2, 'justificacion' => 'Presencial, con verificación', 'calificado_por' => 2],
            ['evento_id' => 13, 'factor_id' => 5, 'nivel' => 5, 'justificacion' => 'Jurisdicción no cooperante (GAFI)', 'calificado_por' => 2],
            ['evento_id' => 13, 'factor_id' => 6, 'nivel' => 4, 'justificacion' => 'Casa de cambio de reciente apertura', 'calificado_por' => 2],
            ['evento_id' => 13, 'factor_id' => 7, 'nivel' => 1, 'justificacion' => 'Coincidencia parcial pendiente de verificar', 'calificado_por' => 2],
            // EV-014
            ['evento_id' => 14, 'factor_id' => 1, 'nivel' => 1, 'justificacion' => '$3,200 dentro de lo habitual', 'calificado_por' => 2],
            ['evento_id' => 14, 'factor_id' => 2, 'nivel' => 0, 'justificacion' => 'Pago mensual recurrente', 'calificado_por' => 2],
            ['evento_id' => 14, 'factor_id' => 3, 'nivel' => 1, 'justificacion' => 'Cuenta monetaria empresarial', 'calificado_por' => 2],
            ['evento_id' => 14, 'factor_id' => 4, 'nivel' => 1, 'justificacion' => 'Banca en línea, cliente verificado', 'calificado_por' => 2],
            ['evento_id' => 14, 'factor_id' => 5, 'nivel' => 0, 'justificacion' => 'Proveedor local', 'calificado_por' => 2],
            ['evento_id' => 14, 'factor_id' => 6, 'nivel' => 1, 'justificacion' => 'Sociedad con ingresos verificados', 'calificado_por' => 2],
            ['evento_id' => 14, 'factor_id' => 7, 'nivel' => 0, 'justificacion' => 'Sin vínculo con PEP ni coincidencias', 'calificado_por' => 2],
            // EV-015
            ['evento_id' => 15, 'factor_id' => 1, 'nivel' => 1, 'justificacion' => '$1,800 consistente con las ventas', 'calificado_por' => 2],
            ['evento_id' => 15, 'factor_id' => 2, 'nivel' => 1, 'justificacion' => 'Depósitos semanales habituales', 'calificado_por' => 2],
            ['evento_id' => 15, 'factor_id' => 3, 'nivel' => 2, 'justificacion' => 'Cuenta monetaria con efectivo', 'calificado_por' => 2],
            ['evento_id' => 15, 'factor_id' => 4, 'nivel' => 1, 'justificacion' => 'Presencial y verificado', 'calificado_por' => 2],
            ['evento_id' => 15, 'factor_id' => 5, 'nivel' => 0, 'justificacion' => 'Operación local', 'calificado_por' => 2],
            ['evento_id' => 15, 'factor_id' => 6, 'nivel' => 1, 'justificacion' => 'Comerciante con ingresos verificados', 'calificado_por' => 2],
            ['evento_id' => 15, 'factor_id' => 7, 'nivel' => 0, 'justificacion' => 'Homónimo descartado, sin vínculo', 'calificado_por' => 2],
            // EV-016
            ['evento_id' => 16, 'factor_id' => 1, 'nivel' => 2, 'justificacion' => '€12,000 por servicios facturados', 'calificado_por' => 2],
            ['evento_id' => 16, 'factor_id' => 2, 'nivel' => 1, 'justificacion' => 'Tres transferencias en el mes', 'calificado_por' => 2],
            ['evento_id' => 16, 'factor_id' => 3, 'nivel' => 3, 'justificacion' => 'Transferencias internacionales recibidas', 'calificado_por' => 2],
            ['evento_id' => 16, 'factor_id' => 4, 'nivel' => 3, 'justificacion' => 'Banca en línea con cliente de reciente relación', 'calificado_por' => 2],
            ['evento_id' => 16, 'factor_id' => 5, 'nivel' => 2, 'justificacion' => 'País cooperante', 'calificado_por' => 2],
            ['evento_id' => 16, 'factor_id' => 6, 'nivel' => 2, 'justificacion' => 'Consultor sin ingresos verificados', 'calificado_por' => 2],
            ['evento_id' => 16, 'factor_id' => 7, 'nivel' => 2, 'justificacion' => 'Coincidencia parcial en sanciones pendiente', 'calificado_por' => 2],
            // EV-017
            ['evento_id' => 17, 'factor_id' => 1, 'nivel' => 3, 'justificacion' => '$4,100: el doble del promedio', 'calificado_por' => 2],
            ['evento_id' => 17, 'factor_id' => 2, 'nivel' => 2, 'justificacion' => 'Una operación mayor al promedio', 'calificado_por' => 2],
            ['evento_id' => 17, 'factor_id' => 3, 'nivel' => 2, 'justificacion' => 'Transferencia al exterior documentada', 'calificado_por' => 2],
            ['evento_id' => 17, 'factor_id' => 4, 'nivel' => 2, 'justificacion' => 'Banca en línea, cliente verificado', 'calificado_por' => 2],
            ['evento_id' => 17, 'factor_id' => 5, 'nivel' => 2, 'justificacion' => 'País con marco LA/FT sólido', 'calificado_por' => 2],
            ['evento_id' => 17, 'factor_id' => 6, 'nivel' => 2, 'justificacion' => 'Negocio formal y documentado', 'calificado_por' => 2],
            ['evento_id' => 17, 'factor_id' => 7, 'nivel' => 0, 'justificacion' => 'Sin vínculo con PEP ni coincidencias', 'calificado_por' => 2],
            // EV-018
            ['evento_id' => 18, 'factor_id' => 1, 'nivel' => 4, 'justificacion' => '$56,000 entre sociedades relacionadas', 'calificado_por' => 2],
            ['evento_id' => 18, 'factor_id' => 2, 'nivel' => 3, 'justificacion' => 'Frecuencia por confirmar con el cliente', 'calificado_por' => 2],
            ['evento_id' => 18, 'factor_id' => 5, 'nivel' => 3, 'justificacion' => 'Jurisdicción bajo monitoreo intensificado', 'calificado_por' => 2],
            // EV-019
            ['evento_id' => 19, 'factor_id' => 1, 'nivel' => 4, 'justificacion' => '$7,400: muy superior a su retiro habitual', 'calificado_por' => 2],
            ['evento_id' => 19, 'factor_id' => 2, 'nivel' => 1, 'justificacion' => 'Una sola operación', 'calificado_por' => 2],
        ]);
    }
}
