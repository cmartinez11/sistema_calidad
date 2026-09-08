<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PNC {{ $pnc->codigo_pnc }} - FE-SIG-FOR-30-V</title>
    <style>
        @page {
            margin: 12px 18px;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #111827;
            line-height: 1.2;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-table td {
            border: 1px solid #111827;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .section-box {
            border: 1px solid #111827;
            border-radius: 4px;
            padding: 5px 7px;
            margin-bottom: 5px;
        }
        .section-title {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            font-weight: bold;
            font-size: 10.5px;
            text-transform: uppercase;
            padding: 3px 5px;
            margin-bottom: 5px;
            letter-spacing: 0.3px;
        }
        .field-label {
            font-size: 9px;
            color: #4b5563;
            font-weight: bold;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }
        .field-value {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
        }
        .signature-box {
            border: 1px dashed #9ca3af;
            height: 25px;
            margin-top: 5px;
            text-align: center;
            vertical-align: bottom;
            font-size: 8.5px;
            color: #4b5563;
            padding-bottom: 2px;
            font-weight: bold;
        }
        .grid-2 {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-2 td {
            width: 50%;
            vertical-align: top;
        }
        /* Casillas de Checkbox Compatibles con Motores PDF */
        .chk {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1.2px solid #111827;
            text-align: center;
            line-height: 10px;
            font-size: 9.5px;
            font-weight: bold;
            margin-right: 4px;
            vertical-align: middle;
            background-color: #ffffff;
            color: #111827;
        }
        .chk-active {
            background-color: #111827;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO FORMATO METROLÓGICO FE-SIG-FOR-30-V -->
    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: center; background-color: #f9fafb;">
                <strong style="font-size: 14px; color: #30732B; display: block;">GRUPO FÉNIX</strong>
                <span style="font-size: 8px; color: #4b5563; font-weight: bold;">SISTEMA INTEGRADO DE GESTIÓN</span>
            </td>
            <td style="width: 50%; text-align: center;">
                <strong style="font-size: 12.5px; display: block; letter-spacing: 0.2px;">REPORTE DE PRODUCTO NO CONFORME (PNC)</strong>
                <span style="font-size: 9px; color: #4b5563;">Control de Calidad</span>
            </td>
            <td style="width: 25%; font-size: 8.5px; line-height: 1.3;">
                <strong>Código:</strong> FE-SIG-FOR-30-V<br>
                <strong>Versión:</strong> 01<br>
                <strong>Fecha:</strong> 9/11/2023<br>
                <strong style="color: #dc2626; font-size: 10px;">PNC: {{ $pnc->codigo_pnc }}</strong>
            </td>
        </tr>
    </table>

    <!-- 1. DATOS GENERALES DE LA FALLA -->
    <div class="section-box">
        <div class="section-title">1. Datos Generales de la Falla</div>
        <table style="width: 100%; border-collapse: collapse;">
            <!-- Fila 1: Producto Afectado, Lote Producción, Fecha Emisión -->
            <tr>
                <td style="width: 50%;" colspan="2">
                    <span class="field-label">Producto Afectado</span>
                    <span class="field-value">{{ $pnc->producto->codigo ?? '' }} - {{ $pnc->producto->nombre ?? 'N/A' }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="field-label">Lote Producción</span>
                    <span class="field-value">{{ $pnc->lote->codigo_lote ?? '-' }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="field-label">Fecha Emisión</span>
                    <span class="field-value">{{ $pnc->fecha ? $pnc->fecha->format('d/m/Y') : '-' }}</span>
                </td>
            </tr>
            <!-- Fila 2: Cantidad (1), Cantidad 2 / U.M. 2, Inspección Código -->
            <tr>
                <td style="width: 25%; padding-top: 5px;">
                    <span class="field-label">Cantidad Afectada (1)</span>
                    <span class="field-value" style="color: #dc2626;">
                        {{ number_format($pnc->cantidad, 2) }} {{ $pnc->unidad_medida }}
                    </span>
                </td>
                <td style="width: 25%; padding-top: 5px;">
                    <span class="field-label">Cantidad 2 / U.M. 2</span>
                    <span class="field-value">
                        @if($pnc->cantidad_2 || $pnc->unidad_medida_2)
                            {{ $pnc->cantidad_2 ? number_format($pnc->cantidad_2, 2) : '' }} {{ $pnc->unidad_medida_2 }}
                        @else
                            -
                        @endif
                    </span>
                </td>
                <td style="width: 50%; padding-top: 5px;" colspan="2">
                    <span class="field-label">Inspección Código</span>
                    <span class="field-value">{{ $pnc->codigo_inspeccion ?: 'Manual / Sin Inspección' }}</span>
                </td>
            </tr>
            <!-- Fila 3: Cliente / Proveedor (Ancho Completo - 100%) -->
            <tr>
                <td style="width: 100%; padding-top: 5px;" colspan="4">
                    <span class="field-label">Cliente / Proveedor</span>
                    <span class="field-value">{{ $pnc->cliente_proveedor ?: '-' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- 2. DESCRIPCIÓN DE LA NO CONFORMIDAD DETECTADA -->
    <div class="section-box">
        <div class="section-title">2. Descripción Detallada de la No Conformidad Detectada</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 5px;">
                    <span class="field-label" style="margin-bottom: 2px;">Detalle del Problema / Falla Registrada:</span>
                    <div style="padding: 4px 6px; background-color: #fef2f2; border: 1px solid #fecaca; min-height: 45px; font-size: 10.5px; line-height: 1.3; color: #111827;">
                        {{ $pnc->descripcion_nc }}
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 5px;">
                    <span class="field-label" style="margin-bottom: 2px;">
                        Cavidades con Fallas Detectadas @if(isset($cavidadesDefectuosas) && $cavidadesDefectuosas->count() > 0)({{ $cavidadesDefectuosas->count() }} Afectadas)@endif:
                    </span>
                    @if(isset($cavidadesDefectuosas) && $cavidadesDefectuosas->count() > 0)
                        <table style="width: 100%; border-collapse: collapse; font-size: 9.5px;">
                            <thead>
                                <tr style="background-color: #f3f4f6;">
                                    <th style="border: 1px solid #d1d5db; padding: 3px 4px; text-align: left; width: 25%;">Cavidad</th>
                                    <th style="border: 1px solid #d1d5db; padding: 3px 4px; text-align: left; width: 45%;">Motivo / Observación</th>
                                    <th style="border: 1px solid #d1d5db; padding: 3px 4px; text-align: center; width: 30%;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cavidadesDefectuosas as $cav)
                                    <tr>
                                        <td style="border: 1px solid #e5e7eb; padding: 2px 4px; font-weight: bold;">
                                            Cav. {{ sprintf('%02d', $cav->cavidad_numero) }}
                                        </td>
                                        <td style="border: 1px solid #e5e7eb; padding: 2px 4px;">
                                            {{ $cav->motivo_scrap ?: ($cav->observaciones ?: '-') }}
                                        </td>
                                        <td style="border: 1px solid #e5e7eb; padding: 2px 4px; text-align: center;">
                                            @if($cav->estado === 'FUERA_DE_RANGO')
                                                <span style="color: #dc2626; font-weight: bold;">FUERA RANGO</span>
                                            @elseif($cav->estado === 'OBSERVADO')
                                                <span style="color: #ea580c; font-weight: bold;">OBSERVADO</span>
                                            @elseif($cav->estado === 'PASABLE')
                                                <span style="color: #d97706; font-weight: bold;">PASABLE</span>
                                            @elseif($cav->estado === 'ANULADO')
                                                <span style="color: #6b7280; font-weight: bold;">ANULADO</span>
                                            @else
                                                <span style="color: #dc2626; font-weight: bold;">{{ $cav->estado }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="padding: 4px; background-color: #f9fafb; border: 1px solid #e5e7eb; min-height: 45px; font-size: 9.5px; color: #6b7280; text-align: center;">
                            No hay cavidades registradas con observaciones o inspección no vinculada.
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- 3. DÓNDE SE DETECTÓ Y DÓNDE SE ORIGINÓ -->
    <table class="grid-2" style="margin-bottom: 5px;">
        <tr>
            <td style="padding-right: 3px;">
                <div class="section-box">
                    <div class="section-title">Dónde se Detectó la Falla</div>
                    <div style="font-size: 10px; line-height: 1.35;">
                        <strong>Área:</strong> {{ $pnc->detectado_area }}<br>
                        <strong>Fecha:</strong> {{ $pnc->detectado_fecha ? $pnc->detectado_fecha->format('d/m/Y') : '-' }}<br>
                        <strong>Responsable:</strong> {{ $pnc->detectado_responsable }}<br>
                    </div>
                    <div class="signature-box">Firma Responsable Detección</div>
                </div>
            </td>
            <td style="padding-left: 3px;">
                <div class="section-box">
                    <div class="section-title">Dónde se Originó la No Conformidad</div>
                    <div style="font-size: 10px; line-height: 1.35;">
                        <strong>Área:</strong> {{ $pnc->originado_area }}<br>
                        <strong>Fecha:</strong> {{ $pnc->originado_fecha ? $pnc->originado_fecha->format('d/m/Y') : '-' }}<br>
                        <strong>Responsable:</strong> {{ $pnc->originado_responsable ?: '-' }}<br>
                    </div>
                    <div class="signature-box">Firma Responsable Origen</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- 4. EVALUACIÓN / PRUEBAS REALIZADAS -->
    <div class="section-box">
        <div class="section-title">4. Evaluación / Pruebas Realizadas</div>
        <table style="width: 100%; font-size: 10px;">
            <tr>
                <td style="width: 25%;"><span class="chk {{ $pnc->eval_revision_registros ? 'chk-active' : '' }}">{{ $pnc->eval_revision_registros ? 'X' : '' }}</span> Revisión Registros</td>
                <td style="width: 25%;"><span class="chk {{ $pnc->eval_inspeccion_visual ? 'chk-active' : '' }}">{{ $pnc->eval_inspeccion_visual ? 'X' : '' }}</span> Inspección Visual</td>
                <td style="width: 25%;"><span class="chk {{ $pnc->eval_analisis_pruebas ? 'chk-active' : '' }}">{{ $pnc->eval_analisis_pruebas ? 'X' : '' }}</span> Pruebas Metrológicas</td>
                <td style="width: 25%;"><span class="chk {{ $pnc->eval_otros_check ? 'chk-active' : '' }}">{{ $pnc->eval_otros_check ? 'X' : '' }}</span> Otros: {{ $pnc->eval_otros_texto ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- 5. TRATAMIENTO DE SALIDA NO CONFORME & AUTORIZACIÓN -->
    <div class="section-box">
        <div class="section-title">5. Tratamiento de Salida No Conforme & Autorización</div>
        <table style="width: 100%; font-size: 10px; margin-bottom: 4px;">
            <tr>
                <td style="width: 33%;"><span class="chk {{ $pnc->tratamiento_devolucion ? 'chk-active' : '' }}">{{ $pnc->tratamiento_devolucion ? 'X' : '' }}</span> Devolución</td>
                <td style="width: 33%;"><span class="chk {{ $pnc->tratamiento_reproceso ? 'chk-active' : '' }}">{{ $pnc->tratamiento_reproceso ? 'X' : '' }}</span> Reproceso</td>
                <td style="width: 34%;"><span class="chk {{ $pnc->tratamiento_reclasificado ? 'chk-active' : '' }}">{{ $pnc->tratamiento_reclasificado ? 'X' : '' }}</span> Reclasificado</td>
            </tr>
            <tr>
                <td><span class="chk {{ $pnc->tratamiento_molido ? 'chk-active' : '' }}">{{ $pnc->tratamiento_molido ? 'X' : '' }}</span> Molido / Peletizado</td>
                <td><span class="chk {{ $pnc->tratamiento_desperdicio ? 'chk-active' : '' }}">{{ $pnc->tratamiento_desperdicio ? 'X' : '' }}</span> Desperdicio / Scrap</td>
                <td><span class="chk {{ $pnc->tratamiento_refilado ? 'chk-active' : '' }}">{{ $pnc->tratamiento_refilado ? 'X' : '' }}</span> Refilado</td>
            </tr>
            <tr>
                <td><span class="chk {{ $pnc->tratamiento_concesion ? 'chk-active' : '' }}">{{ $pnc->tratamiento_concesion ? 'X' : '' }}</span> Concesión</td>
                <td><span class="chk {{ $pnc->tratamiento_desviacion ? 'chk-active' : '' }}">{{ $pnc->tratamiento_desviacion ? 'X' : '' }}</span> Desviación</td>
                <td><span class="chk {{ $pnc->tratamiento_otros ? 'chk-active' : '' }}">{{ $pnc->tratamiento_otros ? 'X' : '' }}</span> Otros</td>
            </tr>
        </table>

        <table style="width: 100%; font-size: 10px; border-top: 1px solid #d1d5db; padding-top: 4px;">
            <tr>
                <td style="width: 40%;">
                    <strong>Autorizado Por:</strong><br>
                    <span style="font-size: 10.5px;">{{ $pnc->tratamiento_autorizado_por ?: '' }}</span>
                </td>
                <td style="width: 30%;">
                    <strong>Fecha:</strong><br>
                    <span style="font-size: 10.5px;">{{ $pnc->tratamiento_fecha ? $pnc->tratamiento_fecha->format('d/m/Y') : '' }}</span>
                </td>
                <td style="width: 30%;">
                    <div class="signature-box" style="height: 24px; margin-top: 0;">Firma Autorización Calidad</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 6. ANÁLISIS DE CAUSA RAÍZ (5M) Y ACCIÓN CORRECTIVA -->
    <div class="section-box">
        <div class="section-title">6. Causa Raiz</div>
        <div style="font-size: 10px; margin-bottom: 4px;">
            <strong>Factores Involucrados (5M):</strong> &nbsp;
            <span class="chk {{ $pnc->causa_mano_obra ? 'chk-active' : '' }}">{{ $pnc->causa_mano_obra ? 'X' : '' }}</span> Mano Obra &nbsp;&nbsp;
            <span class="chk {{ $pnc->causa_maquina ? 'chk-active' : '' }}">{{ $pnc->causa_maquina ? 'X' : '' }}</span> Máquina &nbsp;&nbsp;
            <span class="chk {{ $pnc->causa_material ? 'chk-active' : '' }}">{{ $pnc->causa_material ? 'X' : '' }}</span> Material &nbsp;&nbsp;
            <span class="chk {{ $pnc->causa_metodo ? 'chk-active' : '' }}">{{ $pnc->causa_metodo ? 'X' : '' }}</span> Método &nbsp;&nbsp;
            <span class="chk {{ $pnc->causa_medio_ambiente ? 'chk-active' : '' }}">{{ $pnc->causa_medio_ambiente ? 'X' : '' }}</span> Medio Ambiente
        </div>

        <div style="width: 100%; font-size: 10px;">
            <div style="margin-bottom: 4px; width: 100%;">
                <span class="field-label" style="margin-bottom: 2px;">Causa Principal Determinada:</span>
                <div style="width: 98%; background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 4px 6px; min-height: 24px; font-size: 10.5px; box-sizing: border-box;">
                    {{ $pnc->causa_principal ?: '' }}
                </div>
            </div>

            <div style="width: 100%;">
                <span class="field-label" style="margin-bottom: 2px;">Acción Correctiva Imputada:</span>
                <div style="width: 98%; background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 4px 6px; min-height: 24px; font-size: 10.5px; box-sizing: border-box;">
                    {{ $pnc->accion_correctiva ?: '' }}
                </div>
            </div>
        </div>
    </div>

    <!-- 7. PLAN DE ACCIÓN (ESTÁTICO PARA LLENADO MANUAL) -->
    <div class="section-box" style="margin-bottom: 0;">
        <div class="section-title">7. Plan de Acción</div>

        <!-- Tabla Estática para Registro de Actividades -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 9.5px;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="border: 1px solid #d1d5db; padding: 3px 5px; text-align: left; width: 55%;">ACTIVIDAD</th>
                    <th style="border: 1px solid #d1d5db; padding: 3px 5px; text-align: left; width: 25%;">RESPONSABLE</th>
                    <th style="border: 1px solid #d1d5db; padding: 3px 5px; text-align: center; width: 20%;">FECHA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #e5e7eb; height: 20px;"></td>
                    <td style="border: 1px solid #e5e7eb; height: 20px;"></td>
                    <td style="border: 1px solid #e5e7eb; height: 20px;"></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #e5e7eb; height: 20px;"></td>
                    <td style="border: 1px solid #e5e7eb; height: 20px;"></td>
                    <td style="border: 1px solid #e5e7eb; height: 20px;"></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #e5e7eb; height: 20px;"></td>
                    <td style="border: 1px solid #e5e7eb; height: 20px;"></td>
                    <td style="border: 1px solid #e5e7eb; height: 20px;"></td>
                </tr>
            </tbody>
        </table>

        <!-- Subsección Responsable de Validación -->
        <table style="width: 100%; border-collapse: collapse; font-size: 9.5px; border-top: 1px solid #d1d5db; padding-top: 4px;">
            <tr>
                <td style="width: 40%; vertical-align: bottom;">
                    <span class="field-label" style="margin-bottom: 2px;">Nombre del Responsable de Validación:</span>
                    <div style="border-bottom: 1px solid #111827; height: 16px; width: 95%;"></div>
                </td>
                <td style="width: 30%; vertical-align: bottom;">
                    <span class="field-label" style="margin-bottom: 2px;">Fecha de Validación:</span>
                    <div style="border-bottom: 1px solid #111827; height: 16px; width: 90%;"></div>
                </td>
                <td style="width: 30%; vertical-align: top;">
                    <div class="signature-box" style="height: 24px; margin-top: 0;">Firma Responsable Validación</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
