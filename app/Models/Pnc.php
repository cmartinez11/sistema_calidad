<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pnc extends Model
{
    use HasFactory;

    protected $table = 'pnc';

    protected $fillable = [
        'codigo_pnc',
        'codigo_inspeccion',
        'producto_id',
        'lote_id',
        'user_id',
        'fecha',
        'cantidad',
        'unidad_medida',
        'cliente_proveedor',
        'descripcion_nc',
        'detectado_area',
        'detectado_fecha',
        'detectado_responsable',
        'originado_area',
        'originado_fecha',
        'originado_responsable',
        'eval_revision_registros',
        'eval_inspeccion_visual',
        'eval_analisis_pruebas',
        'eval_otros_check',
        'eval_otros_texto',
        'tratamiento_devolucion',
        'tratamiento_reproceso',
        'tratamiento_reclasificado',
        'tratamiento_molido',
        'tratamiento_desperdicio',
        'tratamiento_refilado',
        'tratamiento_concesion',
        'tratamiento_desviacion',
        'tratamiento_otros',
        'tratamiento_autorizado_por',
        'tratamiento_fecha',
        'causa_mano_obra',
        'causa_maquina',
        'causa_material',
        'causa_metodo',
        'causa_medio_ambiente',
        'causa_principal',
        'accion_correctiva',
        'estado_pnc',
    ];

    protected $casts = [
        'fecha' => 'date',
        'detectado_fecha' => 'date',
        'originado_fecha' => 'date',
        'tratamiento_fecha' => 'date',
        'eval_revision_registros' => 'boolean',
        'eval_inspeccion_visual' => 'boolean',
        'eval_analisis_pruebas' => 'boolean',
        'eval_otros_check' => 'boolean',
        'tratamiento_devolucion' => 'boolean',
        'tratamiento_reproceso' => 'boolean',
        'tratamiento_reclasificado' => 'boolean',
        'tratamiento_molido' => 'boolean',
        'tratamiento_desperdicio' => 'boolean',
        'tratamiento_refilado' => 'boolean',
        'tratamiento_concesion' => 'boolean',
        'tratamiento_desviacion' => 'boolean',
        'tratamiento_otros' => 'boolean',
        'causa_mano_obra' => 'boolean',
        'causa_maquina' => 'boolean',
        'causa_material' => 'boolean',
        'causa_metodo' => 'boolean',
        'causa_medio_ambiente' => 'boolean',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inspeccionCalidad(): BelongsTo
    {
        return $this->belongsTo(InspeccionCalidad::class, 'codigo_inspeccion', 'codigo_inspeccion');
    }
}
