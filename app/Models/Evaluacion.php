<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `evaluacion`: evaluación: agrupa eventos y fija la versión de parámetros que se usa.
 */
class Evaluacion extends Model
{
    protected $table = 'evaluacion';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'periodo_inicio',
        'periodo_fin',
        'alcance',
        'responsable_id',
        'version_id',
        'estado',
    ];

    protected $casts = [
        'periodo_inicio' => 'date',
        'periodo_fin'    => 'date',
        'creado_en'      => 'datetime',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(VersionParametros::class, 'version_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'evaluacion_id');
    }
}
