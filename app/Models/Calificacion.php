<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `calificacion`: nivel 0-5 de cada factor por evento, con justificación. `aporte` lo calcula sp_calcular_resultado.
 */
class Calificacion extends Model
{
    protected $table = 'calificacion';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'evento_id',
        'factor_id',
        'nivel',
        'justificacion',
        'dato_faltante',
        'calificado_por',
    ];

    protected $casts = [
        'nivel'         => 'integer',
        'aporte'        => 'decimal:2',
        'dato_faltante' => 'boolean',
        'calificado_en' => 'datetime',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function factor(): BelongsTo
    {
        return $this->belongsTo(Factor::class, 'factor_id');
    }

    public function calificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calificado_por');
    }
}
