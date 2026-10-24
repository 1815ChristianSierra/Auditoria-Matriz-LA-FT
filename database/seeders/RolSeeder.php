<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'administrador',
                'descripcion' => 'Administra factores, escalas y ponderaciones autorizadas.',
            ],
            [
                'nombre' => 'analista',
                'descripcion' => 'Registra eventos y califica factores (1.ª línea).',
            ],
            [
                'nombre' => 'supervisor',
                'descripcion' => 'Revisa eventos de riesgo elevado (2.ª línea).',
            ],
            [
                'nombre' => 'auditor',
                'descripcion' => 'Consulta y verifica evidencia (3.ª línea).',
            ],
        ];
        foreach ($roles as $role) {
            Rol::firstOrCreate(['nombre' => $role['nombre']], $role);
        }
    }
}
