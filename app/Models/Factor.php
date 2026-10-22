<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `factor`: catálogo de factores de riesgo (monto, frecuencia, producto, canal...).
 */
class Factor extends Model
{
    protected $table = 'factor';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ---- Relaciones --------------------------------------------------------

    /** Peso de este factor en cada versión. */
    public function parametros(): HasMany
    {
        return $this->hasMany(ParametroFactor::class, 'factor_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'factor_id');
    }
}
