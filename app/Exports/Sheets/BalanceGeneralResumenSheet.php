<?php

namespace App\Exports\Sheets;

use App\Models\ReporteServicio;
use App\Models\SolicitudServicio;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BalanceGeneralResumenSheet implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Balance General';
    }

    public function headings(): array
    {
        return ['#', 'Métrica Contable', 'Valor / Cantidad'];
    }

    public function array(): array
    {
        $totalClientes = User::where('role', 'cliente')->count();
        $totalMantenimientos = ReporteServicio::count();
        $ingresosManoObra = (float) (ReporteServicio::sum('costo_mano_obra') ?? 0);
        $ingresosRepuestos = (float) (ReporteServicio::sum('costo_repuestos') ?? 0);
        $ingresosMantenimientos = (float) (ReporteServicio::sum('costo_total') ?? 0);

        $solicitudesCompra = SolicitudServicio::where('tipo', 'compra')->get();
        $totalVentasProductos = $solicitudesCompra->count();
        $ingresosVentasProductos = (float) $solicitudesCompra->sum('valor');

        $granTotalFacturado = $ingresosMantenimientos + $ingresosVentasProductos;

        return [
            [1, 'Total Clientes Registrados', $totalClientes],
            [2, 'Total Mantenimientos Realizados', $totalMantenimientos],
            [3, 'Ingresos por Mano de Obra ($)', $ingresosManoObra],
            [4, 'Ingresos por Repuestos en Servicios ($)', $ingresosRepuestos],
            [5, 'Total Facturado por Mantenimientos ($)', $ingresosMantenimientos],
            [6, 'Total Solicitudes de Compra de Productos', $totalVentasProductos],
            [7, 'Total Facturado por Ventas de Inventario ($)', $ingresosVentasProductos],
            [8, 'GRAN TOTAL INGRESOS DEL SISTEMA ($)', $granTotalFacturado],
        ];
    }
}