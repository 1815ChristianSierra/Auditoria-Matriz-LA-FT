<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #14 · Puntos de cada agravante por versión
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametro_agravante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('version_parametros');
            $table->foreignId('agravante_id')->constrained('agravante');
            $table->decimal('puntos', 4, 2);
            $table->unique(['version_id', 'agravante_id'], 'ux_pa_version_agravante');
        });
        DB::statement("ALTER TABLE parametro_agravante ADD CONSTRAINT ck_pa_puntos CHECK (puntos >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('parametro_agravante');
    }
};
