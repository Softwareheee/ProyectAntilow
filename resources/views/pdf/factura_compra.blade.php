<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proforma de compra #{{ $solicitud->id }} - Antilow</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; margin: 0; padding: 34px; font-size: 12px; }
        .header { border-bottom: 3px solid #06b6d4; padding-bottom: 16px; }
        .brand { font-size: 26px; font-weight: bold; letter-spacing: 3px; color: #0f172a; }
        .subtitle { margin-top: 5px; color: #0891b2; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; }
        .document-title { margin-top: 25px; font-size: 20px; font-weight: bold; color: #0f172a; }
        .notice { margin-top: 9px; padding: 10px 12px; border: 1px solid #bae6fd; border-radius: 6px; background: #f0f9ff; color: #075985; font-size: 10px; }
        .info { width: 100%; margin-top: 22px; border-collapse: collapse; }
        .info td { width: 50%; padding: 5px 0; vertical-align: top; line-height: 1.7; }
        .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: .7px; }
        .value { color: #0f172a; font-size: 12px; font-weight: bold; }
        .items { width: 100%; margin-top: 24px; border-collapse: collapse; }
        .items th { padding: 11px 9px; background: #0f172a; color: #fff; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .6px; }
        .items td { padding: 12px 9px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .numeric { text-align: right !important; white-space: nowrap; }
        .total { margin-top: 18px; margin-left: auto; width: 230px; border-top: 2px solid #06b6d4; padding-top: 11px; text-align: right; }
        .total-label { color: #64748b; font-size: 10px; text-transform: uppercase; }
        .total-value { margin-top: 4px; color: #0e7490; font-size: 19px; font-weight: bold; }
        .footer { margin-top: 50px; border-top: 1px solid #e2e8f0; padding-top: 12px; color: #64748b; font-size: 10px; line-height: 1.6; }
    </style>
</head>
<body>
    @php
        $nombreProducto = $solicitud->producto?->nombre
            ?? preg_replace('/^Solicitud de compra:\s*/i', '', $solicitud->asunto);
        $precioUnitario = $solicitud->precio_unitario ?? $solicitud->producto?->precio ?? 0;
        $cantidad = $solicitud->cantidad ?: 1;
        $total = $precioUnitario * $cantidad;
        $nombreCliente = $solicitud->nombre_cliente ?: ($solicitud->user?->name ?? 'Cliente');
    @endphp

    <header class="header">
        <div class="brand">ANTILOW</div>
        <div class="subtitle">Tecnología, soporte y equipos</div>
    </header>

    <div class="document-title">Proforma de compra</div>
    <div class="notice">
        Documento informativo de la solicitud. No es comprobante de pago ni confirma que la compra haya sido completada.
    </div>

    <table class="info">
        <tr>
            <td>
                <div class="label">Cliente</div>
                <div class="value">{{ $nombreCliente }}</div>
            </td>
            <td>
                <div class="label">Solicitud</div>
                <div class="value">#COMP-{{ str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Teléfono de contacto</div>
                <div class="value">{{ $solicitud->telefono_cliente ?: 'No registrado' }}</div>
            </td>
            <td>
                <div class="label">Fecha de solicitud</div>
                <div class="value">{{ $solicitud->created_at->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Producto</th>
                <th class="numeric">Cantidad</th>
                <th class="numeric">Precio unitario</th>
                <th class="numeric">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $nombreProducto }}</td>
                <td class="numeric">{{ $cantidad }}</td>
                <td class="numeric">${{ number_format((float) $precioUnitario, 0, ',', '.') }} COP</td>
                <td class="numeric">${{ number_format((float) $total, 0, ',', '.') }} COP</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        <div class="total-label">Total estimado de la solicitud</div>
        <div class="total-value">${{ number_format((float) $total, 0, ',', '.') }} COP</div>
    </div>

    <footer class="footer">
        El equipo Antilow se comunicará con el cliente para confirmar disponibilidad y continuar con la compra.
        El precio se muestra según el valor registrado al momento de crear la solicitud.
    </footer>
</body>
</html>
