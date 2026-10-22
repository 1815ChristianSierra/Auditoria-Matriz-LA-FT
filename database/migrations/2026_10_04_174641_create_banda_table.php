<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #11 · Bandas de clasificación por versión (Bajo, Medio, Alto, Crítico)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('version_parametros');
            $table->string('nombre', 10);
            $table->smallInteger('orden');                                // 1 = más baja
            $table->decimal('desde', 5, 2);
            $table->decimal('hasta', 5, 2);
            $table->text('tratamiento')->nullable();
            $table->unique(['version_id', 'nombre'], 'ux_banda_nombre');
            $table->unique(['version_id', 'orden'], 'ux_banda_orden');
        });
        DB::statement("ALTER TABLE banda ADD CONSTRAINT ck_banda_rango CHECK (hasta >= desde)");
    }

    public function down(): void
    {
        Schema::dropIfExists('banda');
    }
};
