<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `lista_restrictiva`: listas PEP / sanciones. Solo datos ficticios.
 */
class ListaRestrictiva extends Model
{
    protected $table = 'lista_restrictiva';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'nombre_ficticio',
        'tipo_lista',
        'fuente_ficticia',
        'activo',
    ];

    protected $casts = [
        'activo'    => 'boolean',
        'sintetico' => 'boolean',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function coincidencias(): HasMany
    {
        return $this->hasMany(CoincidenciaLista::class, 'lista_id');
    }
}
