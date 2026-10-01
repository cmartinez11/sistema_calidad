<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatrizEmpaque extends Model
{
    use HasFactory;

    protected $table = 'matriz_empaques';

    protected $fillable = [
        'producto_id',
        'gramaje',
        'presentacion',
        'factor_millares',
        'factor_peso_kg',
        'cant_unidades',
    ];

    protected $casts = [
        'gramaje' => 'float',
        'factor_millares' => 'float',
        'factor_peso_kg' => 'float',
        'cant_unidades' => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
