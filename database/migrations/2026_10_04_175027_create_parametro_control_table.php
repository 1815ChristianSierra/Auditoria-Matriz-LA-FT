<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #15 · Puntos que resta cada control por versión
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametro_control', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('version_parametros');
            $table->foreignId('control_id')->constrained('control');
            $table->decimal('puntos_resta', 4, 2);
            $table->unique(['version_id', 'control_id'], 'ux_pc_version_control');
        });
        DB::statement("ALTER TABLE parametro_control ADD CONSTRAINT ck_pc_puntos CHECK (puntos_resta >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('parametro_control');
    }
};
