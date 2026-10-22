<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #29 · fn_calc_hash, sp_registrar_bitacora, sp_exigir_borrador, sp_exigir_aprobada
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL 8 con registro binario: si no usa root, active antes `SET GLOBAL log_bin_trust_function_creators = 1;`

        DB::unprepared('DROP FUNCTION IF EXISTS fn_calc_hash');

        DB::unprepared(<<<'SQL'
        CREATE FUNCTION fn_calc_hash(
            p_prev      CHAR(64) CHARACTER SET ascii COLLATE ascii_bin,
            p_usuario   BIGINT UNSIGNED,
            p_fecha     DATETIME(6),
            p_accion    VARCHAR(40),
            p_tipo      VARCHAR(30),
            p_entidad   BIGINT UNSIGNED,
            p_ant       JSON,
            p_nuevo     JSON,
            p_motivo    TEXT,
            p_aprobador BIGINT UNSIGNED)
        RETURNS CHAR(64) CHARACTER SET ascii COLLATE ascii_bin DETERMINISTIC NO SQL
        BEGIN
            RETURN SHA2(CONCAT_WS('|',
                IFNULL(CONVERT(p_prev USING utf8mb4), 'GENESIS'),
                p_usuario,
                DATE_FORMAT(p_fecha, '%Y-%m-%dT%H:%i:%s.%f'),
                p_accion,
                p_tipo,
                IFNULL(p_entidad, ''),
                IFNULL(CAST(p_ant   AS CHAR) COLLATE utf8mb4_0900_ai_ci, ''),
                IFNULL(CAST(p_nuevo AS CHAR) COLLATE utf8mb4_0900_ai_ci, ''),
                IFNULL(p_motivo, ''),
                IFNULL(p_aprobador, '')), 256);
        END
        SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_registrar_bitacora');

        DB::unprepared(<<<'SQL'
        CREATE PROCEDURE sp_registrar_bitacora(
            IN  p_usuario  BIGINT UNSIGNED, IN p_accion VARCHAR(40), IN p_tipo VARCHAR(30),
            IN  p_entidad  BIGINT UNSIGNED, IN p_anterior JSON, IN p_nuevo JSON,
            IN  p_motivo   TEXT, IN p_aprobador BIGINT UNSIGNED,
            OUT p_id       BIGINT UNSIGNED)
        BEGIN
            INSERT INTO bitacora (usuario_id, accion, entidad_tipo, entidad_id,
                                  valor_anterior, valor_nuevo, motivo, aprobador_id)
            VALUES (p_usuario, p_accion, p_tipo, p_entidad, p_anterior, p_nuevo, p_motivo, p_aprobador);
            SET p_id = LAST_INSERT_ID();
        END
        SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_exigir_borrador');

        DB::unprepared(<<<'SQL'
        CREATE PROCEDURE sp_exigir_borrador(IN p_version BIGINT UNSIGNED)
        BEGIN
            DECLARE v_estado VARCHAR(10);
            SELECT estado INTO v_estado FROM version_parametros WHERE id = p_version;
            IF v_estado IS NULL OR v_estado <> 'borrador' THEN
                SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Solo se editan parametros de versiones en borrador. Cree una version nueva.';
            END IF;
        END
        SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_exigir_aprobada');

        DB::unprepared(<<<'SQL'
        CREATE PROCEDURE sp_exigir_aprobada(IN p_version BIGINT UNSIGNED)
        BEGIN
            DECLARE v_estado VARCHAR(10);
            SELECT estado INTO v_estado FROM version_parametros WHERE id = p_version;
            IF v_estado IS NULL OR v_estado <> 'aprobada' THEN
                SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'La evaluacion solo puede usar una version de parametros aprobada';
            END IF;
        END
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_exigir_aprobada');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_exigir_borrador');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_registrar_bitacora');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_calc_hash');
    }
};
