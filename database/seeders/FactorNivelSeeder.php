<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FactorNivel;

class FactorNivelSeeder extends Seeder
{
    /**
     * Solo se siembran los extremos (nivel 0 y nivel 5) del diccionario; los niveles 1 a 4 los define el equipo desde "Editar versión".
     */
    public function run(): void
    {
        FactorNivel::insert([
            ['version_id' => 1, 'factor_id' => 1, 'nivel' => 0, 'descripcion' => 'Habitual para el perfil'],
            ['version_id' => 1, 'factor_id' => 1, 'nivel' => 5, 'descripcion' => 'Varias veces el perfil'],
            ['version_id' => 1, 'factor_id' => 2, 'nivel' => 0, 'descripcion' => 'Esporádica'],
            ['version_id' => 1, 'factor_id' => 2, 'nivel' => 5, 'descripcion' => 'Repetición diaria o sostenida'],
            ['version_id' => 1, 'factor_id' => 3, 'nivel' => 0, 'descripcion' => 'Bajo riesgo, como nómina'],
            ['version_id' => 1, 'factor_id' => 3, 'nivel' => 5, 'descripcion' => 'Opaco o de alto riesgo'],
            ['version_id' => 1, 'factor_id' => 4, 'nivel' => 0, 'descripcion' => 'Presencial y verificado'],
            ['version_id' => 1, 'factor_id' => 4, 'nivel' => 5, 'descripcion' => 'Remoto sin verificación'],
            ['version_id' => 1, 'factor_id' => 5, 'nivel' => 0, 'descripcion' => 'Nacional'],
            ['version_id' => 1, 'factor_id' => 5, 'nivel' => 5, 'descripcion' => 'Jurisdicción de alto riesgo'],
            ['version_id' => 1, 'factor_id' => 6, 'nivel' => 0, 'descripcion' => 'Simple y coherente'],
            ['version_id' => 1, 'factor_id' => 6, 'nivel' => 5, 'descripcion' => 'Incoherente con su actividad'],
            ['version_id' => 1, 'factor_id' => 7, 'nivel' => 0, 'descripcion' => 'Sin vínculo'],
            ['version_id' => 1, 'factor_id' => 7, 'nivel' => 5, 'descripcion' => 'Vínculo confirmado'],
        ]);
    }
}
