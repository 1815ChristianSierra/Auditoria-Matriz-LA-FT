<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `parametro_control`: puntos que resta un control dentro de una versión. Clave: `id` + índice único (version_id, control_id).
 */
class ParametroControl extends Model
{
    protected $table = 'parametro_control';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'version_id',
        'control_id',
        'puntos_resta',
    ];

    protected $casts = [
        'puntos_resta' => 'decimal:2',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function version(): BelongsTo
    {
        return $this->belongsTo(VersionParametros::class, 'version_id');
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class, 'control_id');
    }
}
