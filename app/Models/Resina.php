<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InspeccionCavidad;

class Resina extends Model
{
    protected $table = 'resinas';
    protected $fillable =['codigo','nombre', 'activo'];

    public function inspeccionesCavidades(){
        return $this->hasMany(InspeccionCavidad::class, 'resina_id');
    }
}
