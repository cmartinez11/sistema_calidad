<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resina extends Model
{
    use HasFactory;

    protected $table = 'resinas';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function inspeccionesCavidades(): HasMany
    {
        return $this->hasMany(InspeccionCavidad::class, 'resina_id');
    }
}
