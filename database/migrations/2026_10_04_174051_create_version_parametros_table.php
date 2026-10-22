<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #7 · Versiones de parámetros (borrador → propuesta → aprobada/rechazada)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('version_parametros', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10)->unique();                       // 'v1.0', 'v1.1'
            $table->string('estado', 10)->default('borrador');
            $table->date('fecha_vigencia')->nullable();
            $table->decimal('tope_agravantes', 5, 2)->default(10);
            $table->decimal('tope_controles', 5, 2)->default(3);
            $table->smallInteger('puntaje_dato_faltante')->nullable();
            $table->text('motivo');                                       // por qué se propone
            $table->foreignId('proponente_id')->constrained('users');
            $table->foreignId('aprobador_id')->nullable()->constrained('users');
            $table->text('motivo_decision')->nullable();                  // por qué se aprobó/rechazó
            $table->dateTime('creado_en', 6)->useCurrent();
            $table->dateTime('decidido_en', 6)->nullable();
        });
        DB::statement("ALTER TABLE version_parametros ADD CONSTRAINT ck_version_estado CHECK (estado IN ('borrador','propuesta','aprobada','rechazada','obsoleta'))");
        DB::statement("ALTER TABLE version_parametros ADD CONSTRAINT ck_version_topes CHECK (tope_agravantes >= 0 AND tope_controles >= 0)");
        DB::statement("ALTER TABLE version_parametros ADD CONSTRAINT ck_version_pdf CHECK (puntaje_dato_faltante IS NULL OR puntaje_dato_faltante BETWEEN 0 AND 5)");
        DB::statement("ALTER TABLE version_parametros ADD CONSTRAINT ck_version_separacion CHECK (aprobador_id IS NULL OR aprobador_id <> proponente_id)");
        DB::statement("ALTER TABLE version_parametros ADD CONSTRAINT ck_version_decision CHECK (estado NOT IN ('aprobada','rechazada') OR aprobador_id IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::dropIfExists('version_parametros');
    }
};
