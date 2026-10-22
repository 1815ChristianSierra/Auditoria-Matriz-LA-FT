<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `decision_seguimiento`: decisión humana y plan de acción sobre un resultado. El sistema nunca la toma por sí solo.
 */
class DecisionSeguimiento extends Model
{
    protected $table = 'decision_seguimiento';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'resultado_id',
        'decision',
        'justificacion',
        'decidido_por',
        'responsable_plan',
        'plan_accion',
        'fecha_compromiso',
        'estado_seguimiento',
        'evidencia',
    ];

    protected $casts = [
        'fecha_compromiso' => 'date',
        'decidido_en'      => 'datetime',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function resultado(): BelongsTo
    {
        return $this->belongsTo(Resultado::class, 'resultado_id');
    }

    public function decisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decidido_por');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_plan');
    }
}
