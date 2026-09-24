<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $reporte->id }} - Antilow</title>
    <style>
        body { font-family: sans-serif; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #ef4444; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #1e293b; }
        .title { color: #ef4444; font-size: 14px; text-transform: uppercase; }
        .info-grid { width: 100%; margin-top: 20px; }
        .info-grid td { vertical-align: top; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        .table th { background-color: #0f172a; color: #fff; }
        .total-box { margin-top: 20px; text-align: right; font-size: 16px; font-weight: bold; color: #059669; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">ANTILOW</div>
        <div class="title">Soporte Técnico & Mantenimiento</div>
    </div>

    <table class="info-grid">
        <tr>
            <td>
                <strong>Facturado a:</strong><br>
                {{ $reporte->solicitudservicio->user->name }}<br>
                {{ $reporte->solicitudservicio->user->email }}
            </td>
            <td style="text-align: right;">
                <strong>Factura N°:</strong> #FAC-00{{ $reporte->id }}<br>
                <strong>Fecha:</strong> {{ $reporte->created_at->format('d/m/Y') }}<br>
                <strong>Atendido por:</strong> {{ $reporte->administrador->name }}
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Servicio / Concepto</th>
                <th>Diagnóstico</th>
                <th>Mano de Obra</th>
                <th>Repuestos</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $reporte->solicitudservicio->asunto }}</td>
                <td>{{ $reporte->diagnostico }}</td>
                <td>${{ number_format($reporte->costo_mano_obra, 2) }}</td>
                <td>${{ number_format($reporte->costo_repuestos, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        TOTAL A PAGAR: ${{ number_format($reporte->costo_total, 2) }} COP
    </div>
</body>
</html>