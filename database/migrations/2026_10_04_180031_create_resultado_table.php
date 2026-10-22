<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #25 · Resultado calculado por evento (historial; un solo vigente)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('evento');
            $table->decimal('riesgo_inherente', 6, 2);
            $table->decimal('puntos_agravantes', 5, 2)->default(0);
            $table->decimal('puntos_controles', 5, 2)->default(0);
            $table->decimal('riesgo_residual', 6, 2);
            $table->string('banda', 10);
            $table->foreignId('regla_id')->nullable()->constrained('parametro_regla');   // regla de escalamiento activada
            $table->boolean('vigente')->default(true);
            $table->foreignId('calculado_por')->nullable()->constrained('users');
            $table->dateTime('calculado_en', 6)->useCurrent();
            // Columna técnica: MySQL no tiene índices parciales; esto garantiza un solo resultado vigente por evento.
            $table->unsignedBigInteger('evento_vigente')->nullable()
                  ->storedAs('IF(vigente, evento_id, NULL)')->unique('ux_resultado_vigente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultado');
    }
};
