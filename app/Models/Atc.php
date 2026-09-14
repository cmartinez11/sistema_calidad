<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Atc extends Model
{
    use HasFactory, Auditable;

    protected $table = 'atcs';

    protected $fillable = [
        'correlativo',
        'fecha',
        'cliente_nombre',
        'cliente_ruc',
        'tipo',
        'producto',
        'cantidad',
        'descripcion',
        'lote',
        'accion_correctiva',
        'plan_accion',
        'pnc_id',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'decimal:2',
    ];

    /**
     * Relación con el usuario de calidad que registró el ATC.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación opcional con el registro de Producto No Conforme (PNC).
     */
    public function pnc(): BelongsTo
    {
        return $this->belongsTo(Pnc::class, 'pnc_id');
    }
}
