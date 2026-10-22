<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabla `rol`: roles de la aplicación (analista, supervisor, administrador, auditor).
 */
class Rol extends Model
{
    protected $table = 'rol';

    // La tabla maneja sus propias columnas de fecha (creado_en, etc.), no created_at/updated_at.
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];


    // ---- Relaciones --------------------------------------------------------

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
