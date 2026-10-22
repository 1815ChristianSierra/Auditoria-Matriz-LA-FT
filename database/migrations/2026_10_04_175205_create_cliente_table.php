<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #17 · Clientes (solo datos ficticios)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre_ficticio', 120);
            $table->string('tipo_persona', 10);
            $table->text('actividad')->nullable();
            $table->date('fecha_inicio_relacion')->nullable();
            $table->boolean('ingresos_verificados')->default(false);
            $table->string('jurisdiccion_residencia', 60)->nullable();
            $table->boolean('es_pep')->default(false);
            $table->text('vinculo_pep')->nullable();
            $table->boolean('sintetico')->default(true);
        });
        DB::statement("ALTER TABLE cliente ADD CONSTRAINT ck_cliente_tipo CHECK (tipo_persona IN ('individual','juridica'))");
        DB::statement("ALTER TABLE cliente ADD CONSTRAINT ck_cliente_sintetico CHECK (sintetico = 1)");
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};
