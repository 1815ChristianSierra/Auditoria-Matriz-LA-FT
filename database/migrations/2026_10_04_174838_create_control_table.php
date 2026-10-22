<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #13 · Catálogo de controles compensatorios
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->string('tipo', 12)->nullable();
            $table->string('frecuencia', 40)->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('users');
            $table->string('calif_diseno', 10)->nullable();
            $table->string('calif_operacion', 10)->nullable();
            $table->boolean('activo')->default(true);
        });
        DB::statement("ALTER TABLE control ADD CONSTRAINT ck_control_tipo CHECK (tipo IS NULL OR tipo IN ('preventivo','detectivo','correctivo'))");
        DB::statement("ALTER TABLE control ADD CONSTRAINT ck_control_diseno CHECK (calif_diseno IS NULL OR calif_diseno IN ('adecuado','mejorable','deficiente'))");
        DB::statement("ALTER TABLE control ADD CONSTRAINT ck_control_operacion CHECK (calif_operacion IS NULL OR calif_operacion IN ('adecuado','mejorable','deficiente'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('control');
    }
};
