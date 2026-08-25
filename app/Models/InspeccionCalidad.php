<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspeccionCalidad extends Model
{
    use HasFactory;

    protected $table = 'inspecciones_calidad';

    protected $fillable = [
        'lote_id',
        'maquina_id',
        'user_id',
        'turno_id',
        'peso_min',
        'peso_max',
        'esp_pared_min',
        'esp_pared_max',
        'esp_fondo_min',
        'esp_fondo_max',
        'altura_min',
        'altura_max',
        'estado_evaluacion',
        'desviacion',
        'causa',
        'comentarios',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(Maquina::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function cavidades(): HasMany
    {
        return $this->hasMany(InspeccionCavidad::class, 'inspeccion_id');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(AlertaCalidad::class, 'inspeccion_id');
    }
}
