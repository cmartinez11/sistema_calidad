<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Maquina extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'estado',
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function inspecciones(): HasMany
    {
        return $this->hasMany(InspeccionCalidad::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(AlertaCalidad::class);
    }
}
