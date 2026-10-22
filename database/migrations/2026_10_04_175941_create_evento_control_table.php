<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #24 · Controles compensatorios aplicados a cada evento, con evidencia
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_control', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('evento');
            $table->foreignId('control_id')->constrained('control');
            $table->text('evidencia');
            $table->decimal('puntos_aplicados', 4, 2)->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users');
            $table->dateTime('registrado_en', 6)->useCurrent();
            $table->unique(['evento_id', 'control_id'], 'ux_ec_evento_control');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_control');
    }
};
