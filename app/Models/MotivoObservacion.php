<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoObservacion extends Model
{
    use HasFactory;

    protected $table = 'motivos_observacion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function inspeccionesCalidad(): HasMany
    {
        return $this->hasMany(InspeccionCalidad::class, 'motivo_observacion_id');
    }
}
