<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #28 · Tabla técnica de una fila: ancla de la cadena de hashes
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_cadena', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->char('ultimo_hash', 64)->nullable()->charset('ascii')->collation('ascii_bin');
        });
        DB::statement("ALTER TABLE bitacora_cadena ADD CONSTRAINT ck_cadena_unica CHECK (id = 1)");
        DB::table('bitacora_cadena')->insert(['id' => 1, 'ultimo_hash' => null]);
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_cadena');
    }
};
