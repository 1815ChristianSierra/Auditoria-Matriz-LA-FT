<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #33 · Cálculo reproducible: aporte, inherente, agravantes, controles, residual, banda y escalamiento
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_calcular_resultado');

        DB::unprepared(<<<'SQL'
        CREATE PROCEDURE sp_calcular_resultado(IN p_evento BIGINT UNSIGNED, IN p_usuario BIGINT UNSIGNED,
                                               OUT p_resultado BIGINT UNSIGNED)
        BEGIN
            DECLARE v_version BIGINT UNSIGNED;
            DECLARE v_numero VARCHAR(10);
            DECLARE v_tope_agr, v_tope_ctr DECIMAL(5,2);
            DECLARE v_pdf SMALLINT;
            DECLARE v_faltan INT;
            DECLARE v_inh, v_agr, v_ctr, v_res DECIMAL(8,2);
            DECLARE v_banda VARCHAR(10);
            DECLARE v_orden SMALLINT;
            DECLARE v_regla_id BIGINT UNSIGNED;
            DECLARE v_regla_banda VARCHAR(10);
            DECLARE v_regla_orden SMALLINT;

            SELECT ev.version_id INTO v_version
              FROM evento e JOIN evaluacion ev ON ev.id = e.evaluacion_id
             WHERE e.id = p_evento;
            IF v_version IS NULL THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El evento no existe';
            END IF;

            SELECT numero, tope_agravantes, tope_controles, puntaje_dato_faltante
              INTO v_numero, v_tope_agr, v_tope_ctr, v_pdf
              FROM version_parametros WHERE id = v_version;

            -- 1) Regla de dato faltante: puntaje conservador + marca; el evento nunca se descarta
            SELECT COUNT(*) INTO v_faltan
              FROM parametro_factor pf
             WHERE pf.version_id = v_version
               AND NOT EXISTS (SELECT 1 FROM calificacion c
                                WHERE c.evento_id = p_evento AND c.factor_id = pf.factor_id);
            IF v_faltan > 0 THEN
                IF v_pdf IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Faltan calificaciones y la version no define puntaje de dato faltante';
                END IF;
                INSERT INTO calificacion (evento_id, factor_id, nivel, justificacion, dato_faltante)
                SELECT p_evento, pf.factor_id, v_pdf,
                       CONCAT('Dato faltante: puntaje conservador de la version ', v_numero), TRUE
                  FROM parametro_factor pf
                 WHERE pf.version_id = v_version
                   AND NOT EXISTS (SELECT 1 FROM calificacion c
                                    WHERE c.evento_id = p_evento AND c.factor_id = pf.factor_id);
            END IF;

            -- 2) Aporte por factor y riesgo inherente
            UPDATE calificacion c
              JOIN parametro_factor pf ON pf.version_id = v_version AND pf.factor_id = c.factor_id
               SET c.aporte = ROUND(pf.peso * c.nivel / 5, 2)
             WHERE c.evento_id = p_evento;
            SELECT COALESCE(SUM(aporte), 0) INTO v_inh FROM calificacion WHERE evento_id = p_evento;

            -- 3) Agravantes (suman, con tope)
            UPDATE evento_agravante ea
              JOIN parametro_agravante pa ON pa.version_id = v_version AND pa.agravante_id = ea.agravante_id
               SET ea.puntos_aplicados = pa.puntos
             WHERE ea.evento_id = p_evento;
            SELECT LEAST(COALESCE(SUM(puntos_aplicados), 0), v_tope_agr)
              INTO v_agr FROM evento_agravante WHERE evento_id = p_evento;

            -- 4) Controles compensatorios (restan, con tope)
            UPDATE evento_control ec
              JOIN parametro_control pc ON pc.version_id = v_version AND pc.control_id = ec.control_id
               SET ec.puntos_aplicados = pc.puntos_resta
             WHERE ec.evento_id = p_evento;
            SELECT LEAST(COALESCE(SUM(puntos_aplicados), 0), v_tope_ctr)
              INTO v_ctr FROM evento_control WHERE evento_id = p_evento;

            -- 5) Riesgo residual
            SET v_res = GREATEST(0, LEAST(100, v_inh + v_agr - v_ctr));

            -- 6) Banda por puntaje
            SELECT nombre, orden INTO v_banda, v_orden
              FROM banda WHERE version_id = v_version AND desde <= v_res
             ORDER BY desde DESC LIMIT 1;
            IF v_banda IS NULL THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La version no tiene una banda para ese puntaje';
            END IF;

            -- 7) Reglas de escalamiento (la más severa que se cumpla)
            SELECT r.id, r.banda_forzada, b.orden INTO v_regla_id, v_regla_banda, v_regla_orden
              FROM parametro_regla r
              JOIN calificacion c ON c.evento_id = p_evento AND c.factor_id = r.factor_id
                                 AND c.nivel >= r.nivel_minimo
              JOIN banda b ON b.version_id = r.version_id AND b.nombre = r.banda_forzada
             WHERE r.version_id = v_version
             ORDER BY b.orden DESC LIMIT 1;
            IF v_regla_id IS NOT NULL AND v_regla_orden > v_orden THEN
                SET v_banda = v_regla_banda;
            END IF;

            -- 8) Guardar: el resultado anterior pasa a histórico
            UPDATE resultado SET vigente = 0 WHERE evento_id = p_evento AND vigente = 1;
            INSERT INTO resultado (evento_id, riesgo_inherente, puntos_agravantes, puntos_controles,
                                   riesgo_residual, banda, regla_id, calculado_por)
            VALUES (p_evento, v_inh, v_agr, v_ctr, v_res, v_banda, v_regla_id, p_usuario);
            SET p_resultado = LAST_INSERT_ID();

            INSERT INTO bitacora (usuario_id, accion, entidad_tipo, entidad_id, valor_nuevo, motivo)
            VALUES (p_usuario, 'CALCULAR_RESULTADO', 'resultado', p_resultado,
                    JSON_OBJECT('evento_id', p_evento, 'version', v_numero, 'inherente', v_inh,
                                'agravantes', v_agr, 'controles', v_ctr, 'residual', v_res,
                                'banda', v_banda, 'regla_id', v_regla_id),
                    'Calculo de riesgo inherente y residual');
        END
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_calcular_resultado');
    }
};
