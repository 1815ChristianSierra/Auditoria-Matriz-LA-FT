<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #19 · Cruce cliente vs lista (la confirma una persona)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coincidencia_lista', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('cliente');
            $table->foreignId('lista_id')->constrained('lista_restrictiva');
            $table->string('tipo_coincidencia', 12);
            $table->foreignId('verificada_por')->constrained('users');
            $table->dateTime('verificada_en', 6)->useCurrent();
            $table->text('observacion')->nullable();
            $table->unique(['cliente_id', 'lista_id'], 'ux_coincidencia');
        });
        DB::statement("ALTER TABLE coincidencia_lista ADD CONSTRAINT ck_coin_tipo CHECK (tipo_coincidencia IN ('parcial','confirmada','descartada'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('coincidencia_lista');
    }
};
