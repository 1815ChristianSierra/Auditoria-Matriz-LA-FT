<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #26 · Decisión humana y plan de acción (nunca la toma el sistema)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decision_seguimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resultado_id')->constrained('resultado');
            $table->string('decision', 25);
            $table->text('justificacion');
            $table->foreignId('decidido_por')->constrained('users');
            $table->dateTime('decidido_en', 6)->useCurrent();
            $table->foreignId('responsable_plan')->nullable()->constrained('users');
            $table->text('plan_accion')->nullable();
            $table->date('fecha_compromiso')->nullable();
            $table->string('estado_seguimiento', 10)->nullable();
            $table->text('evidencia')->nullable();
        });
        DB::statement("ALTER TABLE decision_seguimiento ADD CONSTRAINT ck_dec_decision CHECK (decision IN ('en_revision','escalar_a_cumplimiento','descartar','plan_de_accion'))");
        DB::statement("ALTER TABLE decision_seguimiento ADD CONSTRAINT ck_dec_estado CHECK (estado_seguimiento IS NULL OR estado_seguimiento IN ('pendiente','en_curso','cerrado'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('decision_seguimiento');
    }
};
