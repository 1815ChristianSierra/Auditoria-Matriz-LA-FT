<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class ClienteSeeder extends Seeder
{
    /**
     * Clientes ficticios CLI-001 a CLI-016: los cuatro de los eventos sintéticos base y doce más (personas y sociedades, locales y del exterior, con y sin vínculo PEP).
     */
    public function run(): void
    {
        Cliente::insert([
            // Los cuatro clientes de los eventos sintéticos base
            ['id' => 1, 'codigo' => 'CLI-001', 'nombre_ficticio' => 'Asalariado Ficticio A', 'tipo_persona' => 'individual', 'actividad' => 'Empleado asalariado (cuenta de nómina)', 'fecha_inicio_relacion' => '2019-01-15', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Local', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 2, 'codigo' => 'CLI-002', 'nombre_ficticio' => 'Comercial Ficticia B', 'tipo_persona' => 'individual', 'actividad' => 'Negocio formal y documentado', 'fecha_inicio_relacion' => '2023-03-01', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Local', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 3, 'codigo' => 'CLI-003', 'nombre_ficticio' => 'Holding Ficticio C S.A.', 'tipo_persona' => 'juridica', 'actividad' => 'Estructura societaria compleja', 'fecha_inicio_relacion' => '2024-06-10', 'ingresos_verificados' => 0, 'jurisdiccion_residencia' => 'Local', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 4, 'codigo' => 'CLI-004', 'nombre_ficticio' => 'Importadora Ficticia D', 'tipo_persona' => 'juridica', 'actividad' => 'Actividad de alto riesgo', 'fecha_inicio_relacion' => '2025-11-20', 'ingresos_verificados' => 0, 'jurisdiccion_residencia' => 'Extranjero', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            // Clientes de la segunda evaluación y de la evaluación cerrada
            ['id' => 5, 'codigo' => 'CLI-005', 'nombre_ficticio' => 'Ricardo Ficticio Alvarado', 'tipo_persona' => 'individual', 'actividad' => 'Funcionario de gobierno (cargo de alto nivel)', 'fecha_inicio_relacion' => '2021-04-12', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Local', 'es_pep' => 1, 'vinculo_pep' => 'Director de una entidad pública (dato sintético)', 'sintetico' => 1],
            ['id' => 6, 'codigo' => 'CLI-006', 'nombre_ficticio' => 'Servicios Ficticios E S.A.', 'tipo_persona' => 'juridica', 'actividad' => 'Servicios de consultoría empresarial', 'fecha_inicio_relacion' => '2022-08-30', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Local', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 7, 'codigo' => 'CLI-007', 'nombre_ficticio' => 'Remesadora Ficticia F', 'tipo_persona' => 'juridica', 'actividad' => 'Remesas y envío de dinero', 'fecha_inicio_relacion' => '2025-02-14', 'ingresos_verificados' => 0, 'jurisdiccion_residencia' => 'Zona fronteriza / alto riesgo', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 8, 'codigo' => 'CLI-008', 'nombre_ficticio' => 'Marta Ficticia Cifuentes', 'tipo_persona' => 'individual', 'actividad' => 'Comerciante independiente', 'fecha_inicio_relacion' => '2020-11-03', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Local', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 9, 'codigo' => 'CLI-009', 'nombre_ficticio' => 'Inversiones Ficticias Delta', 'tipo_persona' => 'juridica', 'actividad' => 'Inversiones inmobiliarias', 'fecha_inicio_relacion' => '2024-01-22', 'ingresos_verificados' => 0, 'jurisdiccion_residencia' => 'Zona offshore / régimen especial', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 10, 'codigo' => 'CLI-010', 'nombre_ficticio' => 'Pedro Ficticio Lemus', 'tipo_persona' => 'individual', 'actividad' => 'Estudiante universitario (cuenta de ahorro)', 'fecha_inicio_relacion' => '2023-09-05', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Local', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 11, 'codigo' => 'CLI-011', 'nombre_ficticio' => 'Exportadora Ficticia Omega', 'tipo_persona' => 'juridica', 'actividad' => 'Exportación de productos agrícolas', 'fecha_inicio_relacion' => '2018-06-18', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Internacional estándar', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 12, 'codigo' => 'CLI-012', 'nombre_ficticio' => 'Lucía Ficticia Barrios', 'tipo_persona' => 'individual', 'actividad' => 'Comerciante, cónyuge de un funcionario público', 'fecha_inicio_relacion' => '2022-05-27', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Local', 'es_pep' => 1, 'vinculo_pep' => 'Cónyuge de un alto funcionario (dato sintético)', 'sintetico' => 1],
            ['id' => 13, 'codigo' => 'CLI-013', 'nombre_ficticio' => 'Casa de Cambio Ficticia G', 'tipo_persona' => 'juridica', 'actividad' => 'Compra y venta de divisas', 'fecha_inicio_relacion' => '2025-07-09', 'ingresos_verificados' => 0, 'jurisdiccion_residencia' => 'Jurisdicción no cooperante (GAFI)', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 14, 'codigo' => 'CLI-014', 'nombre_ficticio' => 'Transportes Ficticios H', 'tipo_persona' => 'juridica', 'actividad' => 'Transporte de carga regional', 'fecha_inicio_relacion' => '2019-03-11', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Extranjero', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 15, 'codigo' => 'CLI-015', 'nombre_ficticio' => 'Andrés Ficticio Quiroa', 'tipo_persona' => 'individual', 'actividad' => 'Profesional independiente (consultor)', 'fecha_inicio_relacion' => '2024-10-01', 'ingresos_verificados' => 0, 'jurisdiccion_residencia' => 'Extranjero', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
            ['id' => 16, 'codigo' => 'CLI-016', 'nombre_ficticio' => 'Fundación Ficticia Esperanza', 'tipo_persona' => 'juridica', 'actividad' => 'Organización sin fines de lucro', 'fecha_inicio_relacion' => '2021-12-15', 'ingresos_verificados' => 1, 'jurisdiccion_residencia' => 'Local', 'es_pep' => 0, 'vinculo_pep' => null, 'sintetico' => 1],
        ]);
    }
}
