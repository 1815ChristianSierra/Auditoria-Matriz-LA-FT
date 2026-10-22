<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Tabla `evento`: evento sintético a evaluar.
 */
class Evento extends Model
{
    protected $table = 'evento';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'evaluacion_id',
        'cliente_id',
        'descripcion',
        'fecha_evento',
        'monto',
        'moneda',
        'frecuencia_descripcion',
        'producto',
        'canal',
        'jurisdiccion',
        'tipo_riesgo',
        'creado_por',
    ];

    protected $casts = [
        'fecha_evento' => 'date',
        'monto'        => 'decimal:2',
        'creado_en'    => 'datetime',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Evaluacion::class, 'evaluacion_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'evento_id');
    }

    public function agravantesAplicados(): HasMany
    {
        return $this->hasMany(EventoAgravante::class, 'evento_id');
    }

    public function controlesAplicados(): HasMany
    {
        return $this->hasMany(EventoControl::class, 'evento_id');
    }

    /** Historial completo de resultados del evento. */
    public function resultados(): HasMany
    {
        return $this->hasMany(Resultado::class, 'evento_id');
    }

    /** El resultado vigente (la BD garantiza que solo hay uno por evento). */
    public function resultadoVigente(): HasOne
    {
        return $this->hasOne(Resultado::class, 'evento_id')->where('vigente', true);
    }
}
