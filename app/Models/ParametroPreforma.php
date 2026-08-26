<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParametroPreforma extends Model
{
    use HasFactory;

    protected $table = 'parametros_preforma';

    protected $fillable = [
        'producto_id',
        'numero_cavidades',
        'peso_nominal',
        'gramaje',
        'peso_min',
        'peso_max',
        'esp_pared_min',
        'esp_pared_max',
        'esp_fondo_min',
        'esp_fondo_max',
        'altura_min',
        'altura_max',
        'activo',
    ];

    /**
     * Accesor para exponer gramaje si se llama peso_nominal o viceversa
     */
    public function getGramajeAttribute(): ?float
    {
        return $this->attributes['gramaje'] ?? $this->attributes['peso_nominal'] ?? null;
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
