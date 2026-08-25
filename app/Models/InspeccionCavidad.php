<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeccionCavidad extends Model
{
    use HasFactory;

    protected $table = 'inspecciones_cavidades';

    protected $fillable = [
        'inspeccion_id',
        'cavidad_numero',
        'peso_medido',
        'estado',
    ];

    public function inspeccion(): BelongsTo
    {
        return $this->belongsTo(InspeccionCalidad::class);
    }
}
