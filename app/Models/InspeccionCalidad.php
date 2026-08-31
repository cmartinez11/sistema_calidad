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
        'codigo_inspeccion',
        'producto_id',
        'lote_id',
        'maquina_id',
        'molde_id',
        'resina_id',
        'user_id',
        'turno_id',
        'operario_id',
        'peso_min',
        'peso_max',
        'esp_pared_medido',
        'esp_pared_min',
        'esp_pared_max',
        'esp_fondo_medido',
        'esp_fondo_min',
        'esp_fondo_max',
        'altura_medida',
        'altura_min',
        'altura_max',
        'estado_evaluacion',
        'motivo_scrap',
        'desviacion',
        'causa',
        'comentarios',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function molde(): BelongsTo
    {
        return $this->belongsTo(Molde::class, 'molde_id');
    }

    public function resina(): BelongsTo
    {
        return $this->belongsTo(Resina::class, 'resina_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(Maquina::class, 'maquina_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    public function operario(): BelongsTo
    {
        return $this->belongsTo(Operario::class, 'operario_id');
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
