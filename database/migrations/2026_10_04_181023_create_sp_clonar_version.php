<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración #32 · Crea una versión nueva (borrador) copiando otra
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_clonar_version');

        DB::unprepared(<<<'SQL'
        CREATE PROCEDURE sp_clonar_version(IN p_origen BIGINT UNSIGNED, IN p_numero VARCHAR(10), IN p_motivo TEXT,
                                           IN p_proponente BIGINT UNSIGNED, OUT p_nueva BIGINT UNSIGNED)
        BEGIN
            INSERT INTO version_parametros (numero, motivo, proponente_id, tope_agravantes, tope_controles, puntaje_dato_faltante)
            SELECT p_numero, p_motivo, p_proponente, tope_agravantes, tope_controles, puntaje_dato_faltante
              FROM version_parametros WHERE id = p_origen;
            SET p_nueva = LAST_INSERT_ID();

            INSERT INTO parametro_factor (version_id, factor_id, peso)
                SELECT p_nueva, factor_id, peso FROM parametro_factor WHERE version_id = p_origen;
            INSERT INTO factor_nivel (version_id, factor_id, nivel, descripcion)
                SELECT p_nueva, factor_id, nivel, descripcion FROM factor_nivel WHERE version_id = p_origen;
            INSERT INTO banda (version_id, nombre, orden, desde, hasta, tratamiento)
                SELECT p_nueva, nombre, orden, desde, hasta, tratamiento FROM banda WHERE version_id = p_origen;
            INSERT INTO parametro_agravante (version_id, agravante_id, puntos)
                SELECT p_nueva, agravante_id, puntos FROM parametro_agravante WHERE version_id = p_origen;
            INSERT INTO parametro_control (version_id, control_id, puntos_resta)
                SELECT p_nueva, control_id, puntos_resta FROM parametro_control WHERE version_id = p_origen;
            INSERT INTO parametro_regla (version_id, codigo, descripcion, factor_id, nivel_minimo, banda_forzada)
                SELECT p_nueva, codigo, descripcion, factor_id, nivel_minimo, banda_forzada
                  FROM parametro_regla WHERE version_id = p_origen;

            INSERT INTO bitacora (usuario_id, accion, entidad_tipo, entidad_id, valor_nuevo, motivo)
            VALUES (p_proponente, 'CREAR_VERSION', 'version_parametros', p_nueva,
                    JSON_OBJECT('numero', p_numero, 'origen_id', p_origen), p_motivo);
        END
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_clonar_version');
    }
};
