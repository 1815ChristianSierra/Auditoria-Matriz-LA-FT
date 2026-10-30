<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,

        ]);
        if (! app()->environment('production')) {
            $this->call(UserSeeder::class);

            // Parámetros base: catálogos y versión v1.0 (se aprueba al final, cuando ya tiene sus parámetros)
            $this->call([
                FactorSeeder::class,
                AgravanteSeeder::class,
                ControlSeeder::class,
                VersionParametrosSeeder::class,
                ParametroFactorSeeder::class,
                FactorNivelSeeder::class,
                BandaSeeder::class,
                ParametroAgravanteSeeder::class,
                ParametroControlSeeder::class,
                ParametroReglaSeeder::class,
                AprobarVersionSeeder::class,
            ]);

            // Datos de ejemplo: clientes, listas, evaluaciones, eventos EV-001 a EV-020, resultados y decisiones
            $this->call([
                ClienteSeeder::class,
                ListaRestrictivaSeeder::class,
                CoincidenciaListaSeeder::class,
                EvaluacionSeeder::class,
                EventoSeeder::class,
                CalificacionSeeder::class,
                EventoAgravanteSeeder::class,
                EventoControlSeeder::class,
                ResultadoSeeder::class,
                DecisionSeguimientoSeeder::class,
            ]);
        }
    }
}
 