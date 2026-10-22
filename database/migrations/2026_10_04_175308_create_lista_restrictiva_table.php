<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #18 · Listas PEP / sanciones (ficticias)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lista_restrictiva', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_ficticio', 120);
            $table->string('tipo_lista', 10);
            $table->string('fuente_ficticia', 120)->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('sintetico')->default(true);
        });
        DB::statement("ALTER TABLE lista_restrictiva ADD CONSTRAINT ck_lista_tipo CHECK (tipo_lista IN ('PEP','SANCIONES'))");
        DB::statement("ALTER TABLE lista_restrictiva ADD CONSTRAINT ck_lista_sintetico CHECK (sintetico = 1)");
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_restrictiva');
    }
};
