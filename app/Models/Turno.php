<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'hora_inicio',
        'hora_fin',
    ];

    public function inspecciones(): HasMany
    {
        return $this->hasMany(InspeccionCalidad::class);
    }
}
