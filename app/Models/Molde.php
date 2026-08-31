<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Molde extends Model
{
    use HasFactory;

    protected $table = 'molde';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'numero_cavidades',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'numero_cavidades' => 'integer',
    ];

    public function inspeccionesCavidades(): HasMany
    {
        return $this->hasMany(InspeccionCavidad::class, 'molde_id');
    }
}
