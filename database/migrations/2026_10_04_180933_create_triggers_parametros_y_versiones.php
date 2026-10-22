<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #31 · Bloqueo de parámetros, ciclo de vida de la versión y regla de evaluación
 */
return new class extends Migration
{
    public function up(): void
    {
        // Los parámetros solo se editan mientras la versión está en 'borrador' (INSERT, UPDATE y DELETE).
        foreach (['parametro_factor', 'factor_nivel', 'banda', 'parametro_agravante', 'parametro_control', 'parametro_regla'] as $t) {
            foreach (['bi', 'bu', 'bd'] as $s) {
                DB::unprepared("DROP TRIGGER IF EXISTS trg_lock_{$t}_{$s}");
            }
            DB::unprepared("CREATE TRIGGER trg_lock_{$t}_bi BEFORE INSERT ON {$t} FOR EACH ROW
                BEGIN CALL sp_exigir_borrador(NEW.version_id); END");
            DB::unprepared("CREATE TRIGGER trg_lock_{$t}_bu BEFORE UPDATE ON {$t} FOR EACH ROW
                BEGIN CALL sp_exigir_borrador(OLD.version_id); CALL sp_exigir_borrador(NEW.version_id); END");
            DB::unprepared("CREATE TRIGGER trg_lock_{$t}_bd BEFORE DELETE ON {$t} FOR EACH ROW
                BEGIN CALL sp_exigir_borrador(OLD.version_id); END");
        }


        DB::unprepared('DROP TRIGGER IF EXISTS trg_version_bi');

        DB::unprepared(<<<'SQL'
        CREATE TRIGGER trg_version_bi BEFORE INSERT ON version_parametros FOR EACH ROW
        BEGIN
            IF NEW.estado <> 'borrador' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Toda version nueva nace en borrador';
            END IF;
        END
        SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_version_bu');

        DB::unprepared(<<<'SQL'
        CREATE TRIGGER trg_version_bu BEFORE UPDATE ON version_parametros FOR EACH ROW
        cuerpo: BEGIN
            DECLARE v_suma DECIMAL(8,2);
            DECLARE v_msg VARCHAR(128);

            -- Versiones cerradas: inmutables (única excepción: aprobada -> obsoleta)
            IF OLD.estado IN ('aprobada','rechazada','obsoleta') THEN
                IF OLD.estado = 'aprobada' AND NEW.estado = 'obsoleta'
                   AND NEW.numero <=> OLD.numero AND NEW.motivo <=> OLD.motivo
                   AND NEW.proponente_id <=> OLD.proponente_id AND NEW.aprobador_id <=> OLD.aprobador_id
                   AND NEW.tope_agravantes <=> OLD.tope_agravantes AND NEW.tope_controles <=> OLD.tope_controles
                   AND NEW.puntaje_dato_faltante <=> OLD.puntaje_dato_faltante THEN
                    LEAVE cuerpo;
                END IF;
                SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'La version esta cerrada y no puede modificarse. Cree una version nueva.';
            END IF;

            IF NEW.estado <> OLD.estado AND NOT (
                   (OLD.estado = 'borrador'  AND NEW.estado = 'propuesta') OR
                   (OLD.estado = 'propuesta' AND NEW.estado IN ('aprobada','rechazada','borrador'))) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transicion de estado no permitida';
            END IF;

            -- Los pesos deben sumar 100 para proponer o aprobar
            IF NEW.estado IN ('propuesta','aprobada') AND NEW.estado <> OLD.estado THEN
                SELECT COALESCE(SUM(peso), 0) INTO v_suma FROM parametro_factor WHERE version_id = NEW.id;
                IF v_suma <> 100 THEN
                    SET v_msg = CONCAT('Los pesos de la version suman ', v_suma, ' y deben sumar 100');
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
                END IF;
            END IF;

            IF NEW.estado IN ('aprobada','rechazada') THEN
                IF NEW.aprobador_id IS NULL OR NEW.aprobador_id = NEW.proponente_id THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Separacion de funciones: quien decide debe ser distinto de quien propone';
                END IF;
                IF NEW.motivo_decision IS NULL OR TRIM(NEW.motivo_decision) = '' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Debe registrarse el motivo de la decision';
                END IF;
                SET NEW.decidido_en = NOW(6);
                IF NEW.estado = 'aprobada' AND NEW.fecha_vigencia IS NULL THEN
                    SET NEW.fecha_vigencia = CURDATE();
                END IF;
            END IF;
        END
        SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_version_au');

        DB::unprepared(<<<'SQL'
        CREATE TRIGGER trg_version_au AFTER UPDATE ON version_parametros FOR EACH ROW
        BEGIN
            IF NEW.estado IN ('aprobada','rechazada') AND NEW.estado <> OLD.estado THEN
                INSERT INTO bitacora (usuario_id, accion, entidad_tipo, entidad_id,
                                      valor_anterior, valor_nuevo, motivo, aprobador_id)
                VALUES (NEW.aprobador_id,
                        IF(NEW.estado = 'aprobada', 'APROBAR_VERSION', 'RECHAZAR_VERSION'),
                        'version_parametros', NEW.id,
                        JSON_OBJECT('estado', OLD.estado),
                        JSON_OBJECT('estado', NEW.estado, 'numero', NEW.numero, 'proponente_id', NEW.proponente_id),
                        NEW.motivo_decision, NEW.aprobador_id);
            END IF;
        END
        SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_evaluacion_bi');

        DB::unprepared(<<<'SQL'
        CREATE TRIGGER trg_evaluacion_bi BEFORE INSERT ON evaluacion FOR EACH ROW
        BEGIN CALL sp_exigir_aprobada(NEW.version_id); END
        SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_evaluacion_bu');

        DB::unprepared(<<<'SQL'
        CREATE TRIGGER trg_evaluacion_bu BEFORE UPDATE ON evaluacion FOR EACH ROW
        BEGIN
            IF NEW.version_id <> OLD.version_id THEN CALL sp_exigir_aprobada(NEW.version_id); END IF;
        END
        SQL);
    }

    public function down(): void
    {
        foreach (['trg_evaluacion_bu', 'trg_evaluacion_bi', 'trg_version_au', 'trg_version_bu', 'trg_version_bi'] as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
        foreach (['parametro_factor', 'factor_nivel', 'banda', 'parametro_agravante', 'parametro_control', 'parametro_regla'] as $t) {
            foreach (['bi', 'bu', 'bd'] as $s) {
                DB::unprepared("DROP TRIGGER IF EXISTS trg_lock_{$t}_{$s}");
            }
        }
    }
};
