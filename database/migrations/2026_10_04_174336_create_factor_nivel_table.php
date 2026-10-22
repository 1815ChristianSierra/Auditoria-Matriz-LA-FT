<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #10 · Escala 0-5 de cada factor, por versión
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factor_nivel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('version_id');
            $table->unsignedBigInteger('factor_id');
            $table->smallInteger('nivel');
            $table->text('descripcion');
            $table->unique(['version_id', 'factor_id', 'nivel'], 'ux_fn_version_factor_nivel');
            $table->foreign(['version_id', 'factor_id'], 'fk_fn_parametro_factor')
                  ->references(['version_id', 'factor_id'])->on('parametro_factor');
        });
        DB::statement("ALTER TABLE factor_nivel ADD CONSTRAINT ck_fn_nivel CHECK (nivel BETWEEN 0 AND 5)");
    }

    public function down(): void
    {
        Schema::dropIfExists('factor_nivel');
    }
};
