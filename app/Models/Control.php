<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `control`: catálogo de controles compensatorios.
 */
class Control extends Model
{
    protected $table = 'control';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'frecuencia',
        'responsable_id',
        'calif_diseno',
        'calif_operacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /** Puntos que resta este control en cada versión. */
    public function parametros(): HasMany
    {
        return $this->hasMany(ParametroControl::class, 'control_id');
    }

    public function eventosAplicados(): HasMany
    {
        return $this->hasMany(EventoControl::class, 'control_id');
    }
}
