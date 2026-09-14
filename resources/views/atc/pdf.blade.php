<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte ATC {{ $atc->correlativo }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 15px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            padding: 8px;
            border: 1px solid #d1d5db;
        }
        .logo-cell {
            width: 25%;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            color: #30732B;
            background-color: #f9fafb;
        }
        .title-cell {
            width: 50%;
            text-align: center;
        }
        .title-cell h1 {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            color: #111827;
        }
        .title-cell p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #6b7280;
        }
        .meta-cell {
            width: 25%;
            font-size: 9px;
        }
        .section-title {
            background-color: #30732B;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 8px;
            font-size: 11px;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-radius: 3px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table th, .info-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        .info-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
            font-size: 10px;
            width: 25%;
        }
        .box-content {
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            padding: 8px;
            min-height: 50px;
            font-size: 10.5px;
            line-height: 1.4;
            white-space: pre-wrap;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
        }
        .badge-reclamo {
            background-color: #ffe4e6;
            color: #9f1239;
        }
        .badge-devolucion {
            background-color: #fef3c7;
            color: #92400e;
        }
        .footer {
            margin-top: 30px;
            width: 100%;
            border-top: 1px solid #d1d5db;
            padding-top: 8px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Table -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                GRUPO FÉNIX
            </td>
            <td class="title-cell">
                <h1>Reporte de Atención al Cliente (ATC)</h1>
                <p>Gestión de Reclamos y Devoluciones - Sistema de Calidad</p>
            </td>
            <td class="meta-cell">
                <b>Correlativo:</b> {{ $atc->correlativo }}<br>
                <b>Fecha:</b> {{ $atc->fecha ? $atc->fecha->format('d/m/Y') : '-' }}<br>
                <b>Página:</b> 1 de 1
            </td>
        </tr>
    </table>

    <!-- 1. Datos Generales -->
    <div class="section-title">1. Información del Evento</div>
    <table class="info-table">
        <tr>
            <th>Correlativo ATC:</th>
            <td><b>{{ $atc->correlativo }}</b></td>
            <th>Tipo de Evento:</th>
            <td>
                @if($atc->tipo === 'Reclamo')
                    <span class="badge badge-reclamo">Reclamo</span>
                @else
                    <span class="badge badge-devolucion">Devolución</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Cliente / Razón Social:</th>
            <td><b>{{ $atc->cliente_nombre }}</b></td>
            <th>RUC del Cliente:</th>
            <td>{{ $atc->cliente_ruc ?? 'N/A' }}</td>
        </tr>
    </table>

    <!-- 2. Detalle del Producto -->
    <div class="section-title">2. Detalle del Producto y Defecto</div>
    <table class="info-table">
        <tr>
            <th>Producto:</th>
            <td>{{ $atc->producto }}</td>
            <th>Cantidad Afectada:</th>
            <td><b>{{ number_format($atc->cantidad, 2) }}</b></td>
        </tr>
        <tr>
            <th>Lote:</th>
            <td>{{ $atc->lote ?? 'N/A' }}</td>
            <th>Registrado Por:</th>
            <td>{{ $atc->user->name ?? 'Usuario de Calidad' }}</td>
        </tr>
    </table>

    <div style="font-weight: bold; margin-top: 8px; margin-bottom: 4px; font-size: 10px;">Descripción del Reclamo / Devolución:</div>
    <div class="box-content">{{ $atc->descripcion }}</div>

    @if($atc->pnc)
        <div class="section-title" style="background-color: #d97706;">Producto No Conforme (PNC) Vinculado</div>
        <table class="info-table">
            <tr>
                <th>Código PNC:</th>
                <td><b>{{ $atc->pnc->codigo_pnc }}</b></td>
                <th>Estado PNC:</th>
                <td><b>{{ $atc->pnc->estado_pnc }}</b></td>
            </tr>
        </table>
    @endif

    <!-- 3. Acciones y Plan de Acción -->
    <div class="section-title">3. Acciones Correctivas y Plan de Acción</div>

    <div style="font-weight: bold; margin-top: 8px; margin-bottom: 4px; font-size: 10px;">Acción Correctiva:</div>
    <div class="box-content">{{ $atc->accion_correctiva ?: 'Sin acción correctiva registrada.' }}</div>

    <div style="font-weight: bold; margin-top: 10px; margin-bottom: 4px; font-size: 10px;">Plan de Acción / Causa Raíz:</div>
    <div class="box-content">{{ $atc->plan_accion ?: 'Sin plan de acción registrado.' }}</div>

    <br><br><br>

    <!-- Firmas -->
    <table style="width: 100%; text-align: center; margin-top: 20px;">
        <tr>
            <td style="width: 50%; padding: 10px;">
                <div style="border-top: 1px solid #000; width: 70%; margin: 0 auto; padding-top: 4px; font-size: 10px; font-weight: bold;">
                    Elaborado por (Aseguramiento de Calidad)<br>
                    <span style="font-weight: normal; font-size: 9px;">{{ $atc->user->name ?? 'Firma' }}</span>
                </div>
            </td>
            <td style="width: 50%; padding: 10px;">
                <div style="border-top: 1px solid #000; width: 70%; margin: 0 auto; padding-top: 4px; font-size: 10px; font-weight: bold;">
                    Jefatura de Calidad / Gerencia<br>
                    <span style="font-weight: normal; font-size: 9px;">V°B° Aprobación</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Grupo Fénix - Sistema de Gestión de Calidad | Documento Generado Automáticamente
    </div>

</body>
</html>
