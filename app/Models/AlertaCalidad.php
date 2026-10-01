<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaCalidad extends Model
{
    use HasFactory;

    protected $table = 'alertas_calidad';

    protected $fillable = [
        'inspeccion_id',
        'maquina_id',
        'tipo_alerta',
        'valor_registrado',
        'limite_permitido',
        'atendida',
        'observaciones_correccion',
    ];

    public function inspeccion(): BelongsTo
    {
        return $this->belongsTo(InspeccionCalidad::class);
    }

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(Maquina::class);
    }
}
