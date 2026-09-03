<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Metrológico {{ $codigo }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 12px;
            border-bottom: 2px solid #30732B;
            padding-bottom: 8px;
        }
        .logo-box {
            background-color: #30732B;
            color: #ffffff;
            font-weight: bold;
            font-size: 16px;
            padding: 6px 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .meta-table {
            margin-bottom: 12px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .meta-table td {
            padding: 6px 10px;
            vertical-align: top;
            width: 25%;
            border-right: 1px solid #e5e7eb;
        }
        .meta-table td:last-child {
            border-right: none;
        }
        .meta-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        .meta-value {
            font-size: 10px;
            color: #111827;
            font-weight: bold;
        }
        .summary-table {
            margin-bottom: 14px;
        }
        .summary-table td {
            width: 25%;
            padding: 0 4px;
        }
        .summary-table td:first-child { padding-left: 0; }
        .summary-table td:last-child { padding-right: 0; }
        
        .summary-box {
            padding: 8px;
            border-radius: 6px;
            text-align: center;
        }
        .box-green {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .box-red {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .box-blue {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        .box-gray {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            color: #374151;
        }
        .box-title {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        .box-num {
            font-size: 13px;
            font-weight: bold;
            font-family: monospace;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .detail-table {
            margin-bottom: 15px;
        }
        .detail-table th {
            background-color: #30732B;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #30732B;
        }
        .detail-table td {
            padding: 5px 8px;
            font-size: 9px;
            border: 1px solid #e5e7eb;
        }
        .row-even {
            background-color: #f9fafb;
        }
        .row-scrap {
            background-color: #fef2f2;
            color: #991b1b;
        }

        .badge-conforme {
            color: #047857;
            font-weight: bold;
        }
        .badge-scrap {
            color: #dc2626;
            font-weight: bold;
        }
        .badge-anulado {
            color: #6b7280;
            font-weight: bold;
        }
        .row-anulado {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        .signatures-table {
            margin-top: 30px;
        }
        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 40px;
        }
        .signature-line {
            border-top: 1px solid #9ca3af;
            padding-top: 4px;
            font-size: 9px;
            font-weight: bold;
            color: #374151;
        }
        .footer-note {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- CABECERA DEL DOCUMENTO -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <table>
                    <tr>
                        <td style="width: 48px; vertical-align: middle;">
                            <div class="logo-box">GF</div>
                        </td>
                        <td style="vertical-align: middle; padding-left: 8px;">
                            <span style="font-size: 14px; font-weight: bold; color: #111827; text-transform: uppercase; display: block;">GRUPO FÉNIX</span>
                            <span style="font-size: 9px; color: #4b5563; font-weight: bold; text-transform: uppercase;">Sistema de Control de Calidad - Reporte Metrológico</span>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: middle;">
                <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-family: monospace; font-size: 11px; font-weight: bold; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                    {{ $codigo }}
                </div>
                <div style="font-size: 8px; color: #6b7280; margin-top: 3px; font-family: monospace;">
                    Emisión: {{ \Carbon\Carbon::parse($header->created_at)->format('d/m/Y h:i:s A') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS GENERALES Y DE CONTEXTO TÉCNICO -->
    <table class="meta-table">
        <tr>
            <td style="width: 20%;">
                <span class="meta-label">Código Producto</span>
                <span class="meta-value" style="font-family: monospace;">{{ $producto->codigo }}</span>
            </td>
            <td style="width: 25%;">
                <span class="meta-label">Nombre del Producto</span>
                <span class="meta-value">{{ $producto->nombre }}</span>
            </td>
            <td style="width: 20%;">
                <span class="meta-label">Inyectora / Máquina</span>
                <span class="meta-value">{{ $header->maquina->codigo ?? 'N/A' }} {{ $header->maquina ? '('.$header->maquina->nombre.')' : '' }}</span>
            </td>
            <td style="width: 20%;">
                <span class="meta-label">Resina</span>
                <span class="meta-value">{{ $resinaObj->nombre ?? ($calidadResumen->resina->nombre ?? ($resinaObj->codigo ?? ($calidadResumen->resina->codigo ?? 'N/A'))) }}</span>
            </td>
            <td style="width: 20%;">
                <span class="meta-label">Operario / Auditor</span>
                <span class="meta-value">{{ $header->operario->nombre ?? 'N/A' }}</span>
            </td>
        </tr>
    </table>

    <!-- RESUMEN ESTADÍSTICO DE PESOS -->
    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-box box-gray">
                    <span class="box-title">Tolerancia Permitida</span>
                    <span class="box-num" style="font-size: 11px;">
                        {{ $param && $param->peso_min > 0 ? $param->peso_min.'g - '.$param->peso_max.'g' : 'Sin límites' }}
                    </span>
                </div>
            </td>
            <td>
                <div class="summary-box box-green">
                    <span class="box-title">Cumplimiento</span>
                    <span class="box-num">{{ $porcentajeConforme }}%</span>
                </div>
            </td>
            <td>
                <div class="summary-box box-red">
                    <span class="box-title">Fuera de Rango</span>
                    <span class="box-num">{{ $fueraDeRangoCount }} / {{ $totalCavidades }}</span>
                </div>
            </td>
            <td>
                <div class="summary-box box-blue">
                    <span class="box-title">Peso Promedio</span>
                    <span class="box-num">{{ $promedioPeso }} g</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- TABLA DETALLADA DE CAVIDADES -->
    <div class="section-title">Detalle de Pesaje Cavidad por Cavidad</div>
    
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 15%; text-align: center;">N° Cavidad</th>
                <th style="width: 20%; text-align: center;">Peso Medido (g)</th>
                <th style="width: 20%; text-align: center;">Rango Tolerancia</th>
                <th style="width: 20%; text-align: center;">Estado</th>
                <th style="width: 25%; text-align: left;">Motivo de Scrap / Defecto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cavidades as $index => $cav)
                <tr class="{{ $cav->estado === 'ANULADO' ? 'row-anulado' : ($cav->estado === 'FUERA_DE_RANGO' ? 'row-scrap' : ($index % 2 === 1 ? 'row-even' : '')) }}">
                    <td style="text-align: center; font-family: monospace; font-weight: bold;">
                        Cavidad {{ sprintf('%02d', $cav->cavidad_numero) }}
                    </td>
                    <td style="text-align: center; font-family: monospace; font-weight: bold;">
                        {{ $cav->estado === 'ANULADO' ? 'N/A' : ($cav->peso_medido !== null ? number_format($cav->peso_medido, 2).' g' : '-') }}
                    </td>
                    <td style="text-align: center; font-family: monospace; color: #6b7280;">
                        {{ $param && $param->peso_min > 0 ? $param->peso_min.' - '.$param->peso_max.' g' : '-' }}
                    </td>
                    <td style="text-align: center;">
                        @if($cav->estado === 'CONFORME')
                            <span class="badge-conforme">• CONFORME</span>
                        @elseif($cav->estado === 'ANULADO')
                            <span class="badge-anulado">• ANULADO</span>
                        @elseif($cav->estado === 'OBSERVADO')
                            <span class="badge-scrap">• OBSERVADO</span>
                        @elseif($cav->estado === 'PASABLE')
                            <span class="badge-conforme">• PASABLE</span>
                        @else
                            <span class="badge-scrap">• FUERA DE RANGO</span>
                        @endif
                    </td>
                    <td style="text-align: left;">
                        @if($cav->estado === 'ANULADO')
                            <span style="color: #4b5563; font-style: italic;">Anulado: {{ $cav->observaciones ?? 'Sin motivo' }}</span>
                        @elseif(in_array($cav->estado, ['FUERA_DE_RANGO', 'OBSERVADO', 'PASABLE']))
                            <strong style="color: #dc2626;">{{ $cav->motivo_scrap ?? 'Sin especificar' }}</strong>
                        @else
                            <span style="color: #9ca3af; font-style: italic;">Sin defecto</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FIRMAS DE CONFORMIDAD -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line">
                    {{ $header->operario->nombre ?? 'Operador de Planta' }}<br>
                    <span style="font-size: 8px; font-weight: normal; color: #6b7280;">Operario / Inspector de Calidad</span>
                </div>
            </td>
            <td>
                <div class="signature-line">
                    Jefatura de Aseguramiento de Calidad<br>
                    <span style="font-size: 8px; font-weight: normal; color: #6b7280;">Firma y Sello de Validación - Grupo Fénix</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- PIE DE PÁGINA -->
    <div class="footer-note">
        Este documento es un reporte oficial metrológico generado automáticamente por el Sistema de Calidad de Grupo Fénix. Documento confidencial de uso interno.
    </div>

</body>
</html>
