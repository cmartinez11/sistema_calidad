<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_lote',
        'producto_id',
        'maquina_id',
        'resina',
        'fecha_produccion',
        'estado_lote',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(Maquina::class);
    }

    public function inspecciones(): HasMany
    {
        return $this->hasMany(InspeccionCalidad::class);
    }
}
