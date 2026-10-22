<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #20 · Evaluaciones (agrupan eventos y fijan la versión de parámetros usada)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluacion', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->text('alcance');
            $table->foreignId('responsable_id')->constrained('users');
            $table->foreignId('version_id')->constrained('version_parametros');
            $table->string('estado', 10)->default('abierta');
            $table->dateTime('creado_en', 6)->useCurrent();
        });
        DB::statement("ALTER TABLE evaluacion ADD CONSTRAINT ck_eval_estado CHECK (estado IN ('abierta','cerrada'))");
        DB::statement("ALTER TABLE evaluacion ADD CONSTRAINT ck_eval_periodo CHECK (periodo_fin >= periodo_inicio)");
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluacion');
    }
};
