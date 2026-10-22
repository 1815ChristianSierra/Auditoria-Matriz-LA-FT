<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla `coincidencia_lista`: cruce cliente vs lista (parcial, confirmada o descartada). La confirma una persona.
 */
class CoincidenciaLista extends Model
{
    protected $table = 'coincidencia_lista';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'lista_id',
        'tipo_coincidencia',
        'verificada_por',
        'observacion',
    ];

    protected $casts = [
        'verificada_en' => 'datetime',
    ];

    // ---- Relaciones --------------------------------------------------------

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function lista(): BelongsTo
    {
        return $this->belongsTo(ListaRestrictiva::class, 'lista_id');
    }

    public function verificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificada_por');
    }
}
