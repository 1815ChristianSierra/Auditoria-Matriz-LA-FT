<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #6 · Adapta la tabla users de Laravel a la matriz (username, rol, estado)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 'name' hace de nombre completo, 'password' de hash y 'created_at' de fecha de alta.
        // Si la tabla ya tiene filas, ejecute `php artisan migrate:fresh` (rol_id y username son obligatorios).
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 40)->unique()->after('name');
            $table->foreignId('rol_id')->after('username')->constrained('rol');
            $table->string('estado', 10)->default('activo')->after('password');
        });
        DB::statement("ALTER TABLE users ADD CONSTRAINT ck_users_estado CHECK (estado IN ('activo','inactivo','bloqueado'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CHECK ck_users_estado');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'rol_id', 'estado']);
        });
    }
};
