<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `banda`: bandas de clasificación por versión (Bajo, Medio, Alto, Crítico).
 */
class Banda extends Model
{
    protected $table = 'banda';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'version_id',
        'nombre',
        'orden',
        'desde',
        'hasta',
        'tratamiento',
    ];

    protected $casts = [
        'orden' => 'integer',
        'desde' => 'decimal:2',
        'hasta' => 'decimal:2',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function version(): BelongsTo
    {
        return $this->belongsTo(VersionParametros::class, 'version_id');
    }

    // ---- Consultas y utilidades ------------------------------------------------

    /** De la banda más baja a la más alta. */
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden');
    }
}
