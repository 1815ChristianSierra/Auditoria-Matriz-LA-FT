<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #16 · Reglas de escalamiento por versión (requiere factor y banda de la misma versión)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametro_regla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('version_parametros');
            $table->string('codigo', 20);
            $table->text('descripcion');
            $table->unsignedBigInteger('factor_id');
            $table->smallInteger('nivel_minimo');
            $table->string('banda_forzada', 10);
            $table->unique(['version_id', 'codigo'], 'ux_regla_codigo');
            $table->foreign(['version_id', 'factor_id'], 'fk_regla_parametro_factor')
                  ->references(['version_id', 'factor_id'])->on('parametro_factor');
            $table->foreign(['version_id', 'banda_forzada'], 'fk_regla_banda')
                  ->references(['version_id', 'nombre'])->on('banda');
        });
        DB::statement("ALTER TABLE parametro_regla ADD CONSTRAINT ck_regla_nivel CHECK (nivel_minimo BETWEEN 0 AND 5)");
    }

    public function down(): void
    {
        Schema::dropIfExists('parametro_regla');
    }
};
