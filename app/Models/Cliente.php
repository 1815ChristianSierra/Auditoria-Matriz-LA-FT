<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `cliente`: clientes. Solo datos ficticios: la columna `sintetico` queda en 1 y la BD rechaza otro valor.
 */
class Cliente extends Model
{
    protected $table = 'cliente';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre_ficticio',
        'tipo_persona',
        'actividad',
        'fecha_inicio_relacion',
        'ingresos_verificados',
        'jurisdiccion_residencia',
        'es_pep',
        'vinculo_pep',
    ];

    protected $casts = [
        'fecha_inicio_relacion' => 'date',
        'ingresos_verificados'  => 'boolean',
        'es_pep'                => 'boolean',
        'sintetico'             => 'boolean',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'cliente_id');
    }

    public function coincidencias(): HasMany
    {
        return $this->hasMany(CoincidenciaLista::class, 'cliente_id');
    }
}
