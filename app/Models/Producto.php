<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'presentacion',
        'millares_presentacion',
        'gramaje',
        'unidad_peso',
        'unidad_dimension',
        'unidad_produccion',
        'factor_conversion_kg',
        'activo',
    ];

    /**
     * Accesor de compatibilidad para peso_nominal -> gramaje
     */
    public function getPesoNominalAttribute(): ?float
    {
        return $this->gramaje ? (float) $this->gramaje : null;
    }

    public function parametros(): HasMany
    {
        return $this->hasMany(ParametroPreforma::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }
}
