<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `evento_control`: control compensatorio aplicado a un evento, con evidencia. Clave: `id` + índice único (evento_id, control_id). `puntos_aplicados` lo llena sp_calcular_resultado.
 */
class EventoControl extends Model
{
    protected $table = 'evento_control';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'evento_id',
        'control_id',
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

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class, 'control_id');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
