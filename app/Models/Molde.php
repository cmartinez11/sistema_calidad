<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\InspeccionCavidad;

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

    public function inspeccionesCavidades()
    {
        return $this->hasMany(\App\Models\InspeccionCavidad::class, 'molde_id');
    }
}
