<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 20px; }
        .card { background-color: #1e293b; border-radius: 12px; padding: 24px; max-width: 600px; margin: 0 auto; border: 1px solid rgba(255,255,255,0.1); }
        .accent { color: #38bdf8; font-weight: bold; }
        .total { font-size: 18px; color: #34d399; font-weight: bold; margin-top: 15px; }
        .footer { font-size: 12px; color: #64748b; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h2>¡Tu servicio ha sido resuelto!</h2>
        <p>Hola <strong class="accent">{{ $reporte->solicitudservicio->user->name }}</strong>,</p>
        <p>El equipo técnico ha completado el mantenimiento correspondiente a tu requerimiento: <strong>{{ $reporte->solicitudservicio->asunto }}</strong>.</p>
        
        <hr style="border-color: rgba(255,255,255,0.1);">
        
        <p><strong>Diagnóstico:</strong> {{ $reporte->diagnostico }}</p>
        <p><strong>Trabajo realizado:</strong> {{ $reporte->trabajo_realizado }}</p>
        
        <div class="total">
            Total Liquidado: ${{ number_format($reporte->costo_total, 2) }} COP
        </div>

        <p style="margin-top: 20px; font-size: 13px; color: #cbd5e1;">
            Encontrarás adjunta la factura en formato PDF con el desglose detallado de la atención.
        </p>

        <div class="footer">
            Antilow - Sistema de Soporte & Mantenimiento
        </div>
    </div>
</body>
</html>