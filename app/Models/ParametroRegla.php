<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `parametro_regla`: reglas de escalamiento por versión: si un factor llega a cierto nivel, se fuerza una banda.
 */
class ParametroRegla extends Model
{
    protected $table = 'parametro_regla';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'version_id',
        'codigo',
        'descripcion',
        'factor_id',
        'nivel_minimo',
        'banda_forzada',
    ];

    protected $casts = [
        'nivel_minimo' => 'integer',
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

    /** Resultados en los que se activó esta regla. */
    public function resultados(): HasMany
    {
        return $this->hasMany(Resultado::class, 'regla_id');
    }

    // ---- Consultas y utilidades ------------------------------------------------
    // Las claves foráneas (version_id, factor_id) y (version_id, banda_forzada) son compuestas:
    // Eloquent no las soporta como relación, así que son métodos (no se usan con with()).

    public function buscarParametroFactor(): ?ParametroFactor
    {
        return ParametroFactor::query()
            ->where('version_id', $this->version_id)
            ->where('factor_id', $this->factor_id)
            ->first();
    }

    /** Banda que esta regla fuerza, dentro de la misma versión. */
    public function buscarBandaForzada(): ?Banda
    {
        return Banda::query()
            ->where('version_id', $this->version_id)
            ->where('nombre', $this->banda_forzada)
            ->first();
    }
}
