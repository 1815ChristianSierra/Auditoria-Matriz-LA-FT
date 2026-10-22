<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `version_parametros`: versión de parámetros (borrador → propuesta → aprobada/rechazada). Una vez cerrada es inmutable (lo exige un trigger).
 */
class VersionParametros extends Model
{
    protected $table = 'version_parametros';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'numero',
        'estado',
        'fecha_vigencia',
        'tope_agravantes',
        'tope_controles',
        'puntaje_dato_faltante',
        'motivo',
        'proponente_id',
        'aprobador_id',
        'motivo_decision',
    ];

    protected $casts = [
        'fecha_vigencia'        => 'date',
        'tope_agravantes'       => 'decimal:2',
        'tope_controles'        => 'decimal:2',
        'puntaje_dato_faltante' => 'integer',
        'creado_en'             => 'datetime',
        'decidido_en'           => 'datetime',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function proponente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proponente_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }

    public function parametrosFactor(): HasMany
    {
        return $this->hasMany(ParametroFactor::class, 'version_id');
    }

    public function niveles(): HasMany
    {
        return $this->hasMany(FactorNivel::class, 'version_id');
    }

    public function bandas(): HasMany
    {
        return $this->hasMany(Banda::class, 'version_id');
    }

    public function parametrosAgravante(): HasMany
    {
        return $this->hasMany(ParametroAgravante::class, 'version_id');
    }

    public function parametrosControl(): HasMany
    {
        return $this->hasMany(ParametroControl::class, 'version_id');
    }

    public function reglas(): HasMany
    {
        return $this->hasMany(ParametroRegla::class, 'version_id');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class, 'version_id');
    }

    // ---- Consultas y utilidades ------------------------------------------------

    /** Solo versiones aprobadas (las únicas que puede usar una evaluación). */
    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    public function esBorrador(): bool
    {
        return $this->estado === 'borrador';
    }

    public function estaAprobada(): bool
    {
        return $this->estado === 'aprobada';
    }
}
