<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `resultado`: resultado calculado de un evento (historial; solo uno vigente por evento).
 */
class Resultado extends Model
{
    protected $table = 'resultado';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    // Un resultado NUNCA se arma a mano: lo genera CALL sp_calcular_resultado(evento, usuario, @id).
    // Por eso no es asignable en masa (Resultado::create([...]) lanza MassAssignmentException).
    protected $guarded = ['*'];

    protected $hidden = [
        'evento_vigente',
    ];

    protected $casts = [
        'riesgo_inherente'  => 'decimal:2',
        'puntos_agravantes' => 'decimal:2',
        'puntos_controles'  => 'decimal:2',
        'riesgo_residual'   => 'decimal:2',
        'vigente'           => 'boolean',
        'calculado_en'      => 'datetime',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    /** Regla de escalamiento que se activó (si hubo). */
    public function regla(): BelongsTo
    {
        return $this->belongsTo(ParametroRegla::class, 'regla_id');
    }

    public function calculador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculado_por');
    }

    public function decisiones(): HasMany
    {
        return $this->hasMany(DecisionSeguimiento::class, 'resultado_id');
    }

    // ---- Consultas y utilidades ------------------------------------------------

    public function scopeVigentes($query)
    {
        return $query->where('vigente', true);
    }
}
