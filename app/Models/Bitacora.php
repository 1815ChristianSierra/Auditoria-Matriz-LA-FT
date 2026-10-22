<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Tabla `bitacora`: bitácora de auditoría. Solo inserción: los triggers de la BD calculan la cadena de hashes y rechazan UPDATE/DELETE.
 */
class Bitacora extends Model
{
    protected $table = 'bitacora';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'accion',
        'entidad_tipo',
        'entidad_id',
        'valor_anterior',
        'valor_nuevo',
        'motivo',
        'aprobador_id',
    ];

    protected $casts = [
        'valor_anterior' => 'array',
        'valor_nuevo'    => 'array',
        'fecha_hora'     => 'datetime',
    ];

    // Doble protección: además del trigger de la BD, el modelo también rechaza modificar o borrar.
    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('La bitácora es de solo inserción: no se puede modificar un registro.');
        });
        static::deleting(function () {
            throw new LogicException('La bitácora es de solo inserción: no se puede borrar un registro.');
        });
    }

    // ---- Relaciones --------------------------------------------------------

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }
}
