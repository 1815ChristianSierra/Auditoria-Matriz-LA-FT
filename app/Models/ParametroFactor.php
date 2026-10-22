<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `parametro_factor`: peso de un factor dentro de una versión (la suma por versión debe ser 100). Clave: `id` autoincremental + índice único (version_id, factor_id).
 */
class ParametroFactor extends Model
{
    protected $table = 'parametro_factor';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'version_id',
        'factor_id',
        'peso',
    ];

    protected $casts = [
        'peso' => 'decimal:2',
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

    /**
     * Escala 0-5 de este factor en esta versión.
     * La clave foránea de factor_nivel es compuesta (version_id, factor_id), que Eloquent no soporta
     * como relación; por eso es un método que devuelve el Builder (no se usa con with()).
     */
    public function buscarNiveles(): Builder
    {
        return FactorNivel::query()
            ->where('version_id', $this->version_id)
            ->where('factor_id', $this->factor_id)
            ->orderBy('nivel');
    }
}
