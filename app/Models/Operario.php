<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InspeccionCalidad;

class Operario extends Model
{
    use HasFactory, Auditable;

    protected $table = 'operarios';

    protected $fillable = [
        'nombre',
        'codigo_operario',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function inspecciones()
    {
        return $this->hasMany(InspeccionCalidad::class, 'operario_id');
    }
}
