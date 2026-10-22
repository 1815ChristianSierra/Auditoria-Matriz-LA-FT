<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #22 · Nivel 0-5 de cada factor por evento, con justificación
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('evento');
            $table->foreignId('factor_id')->constrained('factor');
            $table->smallInteger('nivel');
            $table->text('justificacion');
            $table->decimal('aporte', 6, 2)->nullable();                  // peso * nivel / 5 (lo llena sp_calcular_resultado)
            $table->boolean('dato_faltante')->default(false);
            $table->foreignId('calificado_por')->nullable()->constrained('users');   // NULL = regla de dato faltante
            $table->dateTime('calificado_en', 6)->useCurrent();
            $table->unique(['evento_id', 'factor_id'], 'ux_calificacion');
        });
        DB::statement("ALTER TABLE calificacion ADD CONSTRAINT ck_cal_nivel CHECK (nivel BETWEEN 0 AND 5)");
    }

    public function down(): void
    {
        Schema::dropIfExists('calificacion');
    }
};
