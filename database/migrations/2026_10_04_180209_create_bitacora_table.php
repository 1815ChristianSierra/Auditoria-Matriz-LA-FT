<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #27 · Bitácora de auditoría (solo inserción, con cadena de hashes)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->dateTime('fecha_hora', 6)->useCurrent();
            $table->string('accion', 40);
            $table->string('entidad_tipo', 30);                           // 'evento', 'resultado', 'version_parametros'...
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('valor_anterior')->nullable();
            $table->json('valor_nuevo')->nullable();
            $table->text('motivo')->nullable();
            $table->foreignId('aprobador_id')->nullable()->constrained('users');
            $table->char('hash_anterior', 64)->nullable()->charset('ascii')->collation('ascii_bin');
            $table->char('hash_registro', 64)->charset('ascii')->collation('ascii_bin');
            $table->index(['entidad_tipo', 'entidad_id'], 'ix_bitacora_entidad');
            $table->index('fecha_hora', 'ix_bitacora_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
