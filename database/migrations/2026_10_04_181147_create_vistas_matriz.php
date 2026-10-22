<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #34 · Vistas: ranking de prioridad, explicación del score y verificación de la bitácora
 */
return new class extends Migration
{
    public function up(): void
    {
        // Si una fila aparece en v_bitacora_alterada, la bitácora fue manipulada.
        // (Con `migrate:fresh` use --drop-views; aquí se usa CREATE OR REPLACE por si quedan vistas.)
        DB::unprepared(<<<'SQL'
        CREATE OR REPLACE VIEW v_ranking_prioridad AS
        SELECT ev.codigo  AS evaluacion,
               vp.numero  AS version,
               RANK() OVER (PARTITION BY ev.id ORDER BY b.orden DESC, r.riesgo_residual DESC) AS prioridad,
               e.codigo   AS evento,
               cl.codigo  AS cliente,
               e.descripcion,
               r.riesgo_inherente,
               r.puntos_agravantes,
               r.puntos_controles,
               r.riesgo_residual,
               r.banda,
               pr.codigo  AS regla_escalamiento
          FROM resultado r
          JOIN evento e              ON e.id  = r.evento_id
          JOIN evaluacion ev         ON ev.id = e.evaluacion_id
          JOIN version_parametros vp ON vp.id = ev.version_id
          JOIN banda b               ON b.version_id = ev.version_id AND b.nombre = r.banda
          JOIN cliente cl            ON cl.id = e.cliente_id
          LEFT JOIN parametro_regla pr ON pr.id = r.regla_id
         WHERE r.vigente = 1
        SQL);

        DB::unprepared(<<<'SQL'
        CREATE OR REPLACE VIEW v_explicacion_resultado AS
        SELECT e.codigo AS evento,
               f.nombre AS factor,
               c.nivel,
               pf.peso,
               c.aporte,
               c.dato_faltante,
               c.justificacion
          FROM calificacion c
          JOIN evento e              ON e.id  = c.evento_id
          JOIN evaluacion ev         ON ev.id = e.evaluacion_id
          JOIN factor f              ON f.id  = c.factor_id
          JOIN parametro_factor pf   ON pf.version_id = ev.version_id AND pf.factor_id = c.factor_id
        SQL);

        DB::unprepared(<<<'SQL'
        CREATE OR REPLACE VIEW v_bitacora_alterada AS
        SELECT t.id, t.accion, t.entidad_tipo, t.fecha_hora
          FROM (SELECT b.*, LAG(b.hash_registro) OVER (ORDER BY b.id) AS hash_previo_esperado
                  FROM bitacora b) t
         WHERE NOT (t.hash_anterior <=> t.hash_previo_esperado)
            OR t.hash_registro <> fn_calc_hash(t.hash_previo_esperado, t.usuario_id, t.fecha_hora, t.accion,
                                               t.entidad_tipo, t.entidad_id, t.valor_anterior, t.valor_nuevo,
                                               t.motivo, t.aprobador_id)
        UNION ALL
        SELECT 0, 'CADENA_TRUNCADA', 'bitacora', NULL
          FROM bitacora_cadena c
         WHERE NOT (c.ultimo_hash <=> (SELECT hash_registro FROM bitacora ORDER BY id DESC LIMIT 1))
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_bitacora_alterada');
        DB::unprepared('DROP VIEW IF EXISTS v_explicacion_resultado');
        DB::unprepared('DROP VIEW IF EXISTS v_ranking_prioridad');
    }
};
