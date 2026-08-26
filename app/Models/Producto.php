<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_producto',
        'unidad_medida',
        'peso_unitario',
        'activo',
    ];

    /**
     * Accesores de compatibilidad legacy
     */
    public function getPesoNominalAttribute(): ?float
    {
        return $this->peso_unitario ? (float) $this->peso_unitario : null;
    }

    public function getGramajeAttribute(): ?float
    {
        return $this->peso_unitario ? (float) $this->peso_unitario : null;
    }

    public function parametroPreforma(): HasOne
    {
        return $this->hasOne(ParametroPreforma::class);
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
