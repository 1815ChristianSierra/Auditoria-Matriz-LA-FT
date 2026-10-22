<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #21 · Eventos sintéticos a evaluar
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('evaluacion_id')->constrained('evaluacion');
            $table->foreignId('cliente_id')->constrained('cliente');
            $table->text('descripcion');
            $table->date('fecha_evento')->nullable();
            $table->decimal('monto', 14, 2);
            $table->char('moneda', 3)->default('USD');
            $table->text('frecuencia_descripcion')->nullable();
            $table->string('producto', 80)->nullable();
            $table->string('canal', 80)->nullable();
            $table->string('jurisdiccion', 80)->nullable();
            $table->string('tipo_riesgo', 5);
            $table->foreignId('creado_por')->constrained('users');
            $table->dateTime('creado_en', 6)->useCurrent();
        });
        DB::statement("ALTER TABLE evento ADD CONSTRAINT ck_evento_monto CHECK (monto >= 0)");
        DB::statement("ALTER TABLE evento ADD CONSTRAINT ck_evento_tipo CHECK (tipo_riesgo IN ('LA','FT','LA/FT'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('evento');
    }
};
