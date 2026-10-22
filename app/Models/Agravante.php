<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `agravante`: catálogo de agravantes.
 */
class Agravante extends Model
{
    protected $table = 'agravante';

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

    /** Puntos de este agravante en cada versión. */
    public function parametros(): HasMany
    {
        return $this->hasMany(ParametroAgravante::class, 'agravante_id');
    }

    public function eventosAplicados(): HasMany
    {
        return $this->hasMany(EventoAgravante::class, 'agravante_id');
    }
}
