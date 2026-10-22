<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `parametro_agravante`: puntos de un agravante dentro de una versión. Clave: `id` + índice único (version_id, agravante_id).
 */
class ParametroAgravante extends Model
{
    protected $table = 'parametro_agravante';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'version_id',
        'agravante_id',
        'puntos',
    ];

    protected $casts = [
        'puntos' => 'decimal:2',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function version(): BelongsTo
    {
        return $this->belongsTo(VersionParametros::class, 'version_id');
    }

    public function agravante(): BelongsTo
    {
        return $this->belongsTo(Agravante::class, 'agravante_id');
    }
}
