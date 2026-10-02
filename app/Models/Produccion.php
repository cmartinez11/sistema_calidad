<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produccion extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relaciones

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function operario()
    {
        return $this->belongsTo(Operario::class);
    }

    public function maquina()
    {
        return $this->belongsTo(Maquina::class);
    }

    public function resina()
    {
        return $this->belongsTo(Resina::class);
    }
    public function matrizEmpaque()
    {
        // Como Produccion tiene un producto_id, podemos buscar en matriz_empaques usando ese mismo producto_id
        return $this->hasOne(MatrizEmpaque::class, 'producto_id', 'producto_id');
    }
    public function molde()
    {
        return $this->belongsTo(Molde::class, 'molde_id');
    }
}
