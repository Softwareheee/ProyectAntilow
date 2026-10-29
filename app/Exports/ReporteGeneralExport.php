<?php

namespace App\Exports;

use App\Exports\Sheets\BalanceGeneralResumenSheet;
use App\Exports\Sheets\InventarioVentasDetalleSheet;
use App\Exports\Sheets\MantenimientoDetalleSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteGeneralExport implements WithMultipleSheets, FromArray
{
    use Exportable;

    /**
     * Satisface la interfaz FromArray requerida por el core de Maatwebsite v4
     */
    public function array(): array
    {
        return [];
    }

    /**
     * Define las pestañas individuales del libro contable.
     */
    public function sheets(): array
    {
        return [
            new BalanceGeneralResumenSheet(),
            new MantenimientoDetalleSheet(),
            new InventarioVentasDetalleSheet(),
        ];
    }
}