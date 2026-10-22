<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #9 · Peso de cada factor por versión (la suma debe dar 100)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametro_factor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('version_parametros');
            $table->foreignId('factor_id')->constrained('factor');
            $table->decimal('peso', 5, 2);
            $table->unique(['version_id', 'factor_id'], 'ux_pf_version_factor');
        });
        DB::statement("ALTER TABLE parametro_factor ADD CONSTRAINT ck_pf_peso CHECK (peso BETWEEN 0 AND 100)");
    }

    public function down(): void
    {
        Schema::dropIfExists('parametro_factor');
    }
};
