<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PNC {{ $pnc->codigo_pnc }} - FE-SIG-FOR-30-V</title>
    <style>
        @page {
            margin: 15px;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #111827;
            line-height: 1.2;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            border: 1px solid #111827;
            padding: 4px;
            vertical-align: middle;
        }
        .section-box {
            border: 1px solid #111827;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 8px;
        }
        .section-title {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 3px 5px;
            margin-bottom: 5px;
        }
        .field-label {
            font-size: 7.5px;
            color: #4b5563;
            font-weight: bold;
            text-transform: uppercase;
            display: block;
        }
        .field-value {
            font-size: 8.5px;
            font-weight: bold;
            color: #111827;
        }
        .signature-box {
            border: 1px dashed #9ca3af;
            height: 40px;
            margin-top: 15px;
            text-align: center;
            vertical-align: bottom;
            font-size: 7.5px;
            color: #6b7280;
            padding-bottom: 2px;
        }
        .grid-2 {
            width: 100%;
        }
        .grid-2 td {
            width: 50%;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO FORMATO METROLÓGICO FE-SIG-FOR-30-V -->
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: center; background-color: #f9fafb;">
                <strong style="font-size: 13px; color: #30732B; display: block;">GRUPO FÉNIX</strong>
                <span style="font-size: 7px; color: #6b7280;">SISTEMA INTEGRADO DE GESTIÓN</span>
            </td>
            <td style="width: 50%; text-align: center;">
                <strong style="font-size: 11px; display: block;">REPORTE DE PRODUCTO NO CONFORME (PNC)</strong>
                <span style="font-size: 8px; color: #4b5563;">Control Metrológico y Aseguramiento de Calidad</span>
            </td>
            <td style="width: 25%; font-size: 7.5px; line-height: 1.3;">
                <strong>Código:</strong> FE-SIG-FOR-30-V<br>
                <strong>Versión:</strong> 00<br>
                <strong>Fecha:</strong> 9/11/2023<br>
                <strong style="color: #dc2626; font-size: 8.5px;">PNC: {{ $pnc->codigo_pnc }}</strong>
            </td>
        </tr>
    </table>

    <!-- 1. DATOS GENERALES DE LA FALLA -->
    <div class="section-box">
        <div class="section-title">1. Datos Generales de la Falla</div>
        <table style="width: 100%;">
            <tr>
                <td style="width: 25%;">
                    <span class="field-label">Fecha Emisión</span>
                    <span class="field-value">{{ $pnc->fecha ? $pnc->fecha->format('d/m/Y') : '-' }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="field-label">Auditoría Código</span>
                    <span class="field-value">{{ $pnc->codigo_inspeccion ?: '-' }}</span>
                </td>
                <td style="width: 50%;" colspan="2">
                    <span class="field-label">Producto Afectado</span>
                    <span class="field-value">{{ $pnc->producto->codigo ?? '' }} - {{ $pnc->producto->nombre ?? 'N/A' }}</span>
                </td>
            </tr>
            <tr>
                <td style="width: 25%; pt-2">
                    <span class="field-label">Lote Producción</span>
                    <span class="field-value">{{ $pnc->lote->codigo_lote ?? '-' }}</span>
                </td>
                <td style="width: 25%; pt-2">
                    <span class="field-label">Cantidad Afectada</span>
                    <span class="field-value" style="color: #dc2626;">{{ number_format($pnc->cantidad, 2) }} {{ $pnc->unidad_medida }}</span>
                </td>
                <td style="width: 50%; pt-2" colspan="2">
                    <span class="field-label">Cliente / Proveedor</span>
                    <span class="field-value">{{ $pnc->cliente_proveedor ?: 'Planta Inyección Grupo Fénix' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- 2. DESCRIPCIÓN DE LA NO CONFORMIDAD DETECTADA -->
    <div class="section-box">
        <div class="section-title">2. Descripción de la No Conformidad Detectada</div>
        <div style="padding: 4px; background-color: #fef2f2; border: 1px solid #fecaca; min-height: 40px; font-size: 8.5px;">
            {{ $pnc->descripcion_nc }}
        </div>
    </div>

    <!-- 3. DÓNDE SE DETECTÓ Y DÓNDE SE ORIGINÓ -->
    <table class="grid-2" style="margin-bottom: 8px;">
        <tr>
            <td style="padding-right: 4px;">
                <div class="section-box">
                    <div class="section-title">📍 Dónde se Detectó la Falla</div>
                    <strong>Área:</strong> {{ $pnc->detectado_area }}<br>
                    <strong>Fecha:</strong> {{ $pnc->detectado_fecha ? $pnc->detectado_fecha->format('d/m/Y') : '-' }}<br>
                    <strong>Responsable:</strong> {{ $pnc->detectado_responsable }}<br>
                    <div class="signature-box">Firma Responsable Detección</div>
                </div>
            </td>
            <td style="padding-left: 4px;">
                <div class="section-box">
                    <div class="section-title">🏭 Dónde se Originó la No Conformidad</div>
                    <strong>Área:</strong> {{ $pnc->originado_area }}<br>
                    <strong>Fecha:</strong> {{ $pnc->originado_fecha ? $pnc->originado_fecha->format('d/m/Y') : '-' }}<br>
                    <strong>Responsable:</strong> {{ $pnc->originado_responsable ?: '-' }}<br>
                    <div class="signature-box">Firma Responsable Origen</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- 4. EVALUACIÓN / PRUEBAS REALIZADAS -->
    <div class="section-box">
        <div class="section-title">4. Evaluación / Pruebas Realizadas</div>
        <table style="width: 100%; font-size: 8px;">
            <tr>
                <td style="width: 25%;">{!! $pnc->eval_revision_registros ? '☑' : '☐' !!} Revisión Registros / Proceso</td>
                <td style="width: 25%;">{!! $pnc->eval_inspeccion_visual ? '☑' : '☐' !!} Inspección Visual</td>
                <td style="width: 25%;">{!! $pnc->eval_analisis_pruebas ? '☑' : '☐' !!} Análisis / Pruebas Metrológicas</td>
                <td style="width: 25%;">{!! $pnc->eval_otros_check ? '☑' : '☐' !!} Otros: {{ $pnc->eval_otros_texto ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- 5. TRATAMIENTO DE SALIDA NO CONFORME Y AUTORIZACIÓN -->
    <div class="section-box">
        <div class="section-title">5. Tratamiento de Salida No Conforme & Autorización</div>
        <table style="width: 100%; font-size: 8px; margin-bottom: 6px;">
            <tr>
                <td>{!! $pnc->tratamiento_devolucion ? '☑' : '☐' !!} Devolución</td>
                <td>{!! $pnc->tratamiento_reproceso ? '☑' : '☐' !!} Reproceso</td>
                <td>{!! $pnc->tratamiento_reclasificado ? '☑' : '☐' !!} Reclasificado</td>
            </tr>
            <tr>
                <td>{!! $pnc->tratamiento_molido ? '☑' : '☐' !!} Molido / Peletizado</td>
                <td>{!! $pnc->tratamiento_desperdicio ? '☑' : '☐' !!} Desperdicio / Scrap</td>
                <td>{!! $pnc->tratamiento_refilado ? '☑' : '☐' !!} Refilado</td>
            </tr>
            <tr>
                <td>{!! $pnc->tratamiento_concesion ? '☑' : '☐' !!} Concesión</td>
                <td>{!! $pnc->tratamiento_desviacion ? '☑' : '☐' !!} Desviación</td>
                <td>{!! $pnc->tratamiento_otros ? '☑' : '☐' !!} Otros</td>
            </tr>
        </table>

        <table style="width: 100%; font-size: 8px; border-top: 1px solid #d1d5db; pt-4">
            <tr>
                <td style="width: 40%;">
                    <strong>Autorizado Por:</strong> {{ $pnc->tratamiento_autorizado_por ?: 'Jefatura de Calidad' }}
                </td>
                <td style="width: 30%;">
                    <strong>Fecha:</strong> {{ $pnc->tratamiento_fecha ? $pnc->tratamiento_fecha->format('d/m/Y') : '-' }}
                </td>
                <td style="width: 30%;">
                    <div class="signature-box" style="height: 30px;">Firma Autorización SIG / Calidad</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 6. ANÁLISIS DE CAUSA RAÍZ (5M) Y ACCIÓN CORRECTIVA -->
    <div class="section-box">
        <div class="section-title">6. Análisis de Causa Raíz (5M) y Acción Correctiva</div>
        <div style="font-size: 8px; margin-bottom: 4px;">
            <strong>Factores Involucrados:</strong>
            {!! $pnc->causa_mano_obra ? '☑' : '☐' !!} Mano Obra |
            {!! $pnc->causa_maquina ? '☑' : '☐' !!} Máquina |
            {!! $pnc->causa_material ? '☑' : '☐' !!} Material |
            {!! $pnc->causa_metodo ? '☑' : '☐' !!} Método |
            {!! $pnc->causa_medio_ambiente ? '☑' : '☐' !!} Medio Ambiente
        </div>

        <table class="grid-2" style="font-size: 8px;">
            <tr>
                <td style="padding-right: 4px;">
                    <strong>Causa Principal Determinada:</strong>
                    <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 4px; min-height: 35px;">
                        {{ $pnc->causa_principal ?: 'Sin especificar' }}
                    </div>
                </td>
                <td style="padding-left: 4px;">
                    <strong>Acción Correctiva Imputada:</strong>
                    <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 4px; min-height: 35px;">
                        {{ $pnc->accion_correctiva ?: 'Sin especificar' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
