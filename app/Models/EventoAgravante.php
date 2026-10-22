<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `evento_agravante`: agravante aplicado a un evento, con evidencia. Clave: `id` + índice único (evento_id, agravante_id). `puntos_aplicados` lo llena sp_calcular_resultado.
 */
class EventoAgravante extends Model
{
    protected $table = 'evento_agravante';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'evento_id',
        'agravante_id',
        'evidencia',
        'registrado_por',
    ];

    protected $casts = [
        'puntos_aplicados' => 'decimal:2',
        'registrado_en'    => 'datetime',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function agravante(): BelongsTo
    {
        return $this->belongsTo(Agravante::class, 'agravante_id');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
