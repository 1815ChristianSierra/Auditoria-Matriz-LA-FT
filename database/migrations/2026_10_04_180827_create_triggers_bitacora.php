<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #30 · Cadena de hashes y bitácora de solo inserción
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_hash');

        DB::unprepared(<<<'SQL'
        CREATE TRIGGER trg_bitacora_hash BEFORE INSERT ON bitacora FOR EACH ROW
        BEGIN
            DECLARE v_prev CHAR(64) CHARACTER SET ascii COLLATE ascii_bin;
            -- 1) Bloquea la fila de la cadena (se libera al confirmar la transacción)
            UPDATE bitacora_cadena SET ultimo_hash = ultimo_hash WHERE id = 1;
            -- 2) Lee el último hash ya confirmado por cualquier sesión
            SELECT ultimo_hash INTO v_prev FROM bitacora_cadena WHERE id = 1;
            IF NEW.fecha_hora IS NULL THEN SET NEW.fecha_hora = NOW(6); END IF;
            SET NEW.hash_anterior = v_prev;
            SET NEW.hash_registro = fn_calc_hash(v_prev, NEW.usuario_id, NEW.fecha_hora, NEW.accion,
                                                 NEW.entidad_tipo, NEW.entidad_id, NEW.valor_anterior,
                                                 NEW.valor_nuevo, NEW.motivo, NEW.aprobador_id);
            -- 3) Avanza la cadena
            UPDATE bitacora_cadena SET ultimo_hash = NEW.hash_registro WHERE id = 1;
        END
        SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_no_update');

        DB::unprepared(<<<'SQL'
        CREATE TRIGGER trg_bitacora_no_update BEFORE UPDATE ON bitacora FOR EACH ROW
        BEGIN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La bitacora es de solo insercion: UPDATE no permitido';
        END
        SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_no_delete');

        DB::unprepared(<<<'SQL'
        CREATE TRIGGER trg_bitacora_no_delete BEFORE DELETE ON bitacora FOR EACH ROW
        BEGIN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La bitacora es de solo insercion: DELETE no permitido';
        END
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_no_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_hash');
    }
};
