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
        'unidad_peso',
        'unidad_dimension',
        'unidad_produccion',
        'factor_conversion_kg',
        'activo',
    ];

    public function parametros(): HasMany
    {
        return $this->hasMany(ParametroPreforma::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }
}
