<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaquinaHistorial extends Model
{
    use HasFactory;

    protected $table = 'maquina_historiales';

    protected $fillable = [
        'maquina_id',
        'user_id',
        'estado_anterior',
        'nuevo_estado',
        'motivo_observacion',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'duracion_minutos',
    ];

    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
        'fecha_hora_fin' => 'datetime',
        'duracion_minutos' => 'integer',
    ];

    public function maquina(): BelongsTo
    {
        return $this->belongsTo(Maquina::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Formatea la duración en formato legible (ej: 2h 15m o En curso).
     */
    public function getDuracionFormateadaAttribute(): string
    {
        if (is_null($this->fecha_hora_fin)) {
            $mins = Carbon::parse($this->fecha_hora_inicio)->diffInMinutes(now());
            $horas = floor($mins / 60);
            $restoMins = $mins % 60;
            return "En curso ({$horas}h {$restoMins}m)";
        }

        $mins = $this->duracion_minutos ?? Carbon::parse($this->fecha_hora_inicio)->diffInMinutes($this->fecha_hora_fin);
        $horas = floor($mins / 60);
        $restoMins = $mins % 60;

        if ($horas > 0) {
            return "{$horas}h {$restoMins}m";
        }

        return "{$restoMins} min";
    }
}
