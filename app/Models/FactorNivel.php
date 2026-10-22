<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `factor_nivel`: qué significa cada nivel 0-5 de un factor, por versión. Clave: `id` + índice único (version_id, factor_id, nivel).
 */
class FactorNivel extends Model
{
    protected $table = 'factor_nivel';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'version_id',
        'factor_id',
        'nivel',
        'descripcion',
    ];

    protected $casts = [
        'nivel' => 'integer',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function version(): BelongsTo
    {
        return $this->belongsTo(VersionParametros::class, 'version_id');
    }

    public function factor(): BelongsTo
    {
        return $this->belongsTo(Factor::class, 'factor_id');
    }

    // ---- Consultas y utilidades ------------------------------------------------

    /** Fila de parametro_factor a la que pertenece (clave foránea compuesta; no se usa con with()). */
    public function buscarParametroFactor(): ?ParametroFactor
    {
        return ParametroFactor::query()
            ->where('version_id', $this->version_id)
            ->where('factor_id', $this->factor_id)
            ->first();
    }
}
