<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #23 · Agravantes aplicados a cada evento, con evidencia
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_agravante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('evento');
            $table->foreignId('agravante_id')->constrained('agravante');
            $table->text('evidencia');
            $table->decimal('puntos_aplicados', 4, 2)->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users');
            $table->dateTime('registrado_en', 6)->useCurrent();
            $table->unique(['evento_id', 'agravante_id'], 'ux_ea_evento_agravante');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_agravante');
    }
};
