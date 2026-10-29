<?php

namespace App\Exports\Sheets;

use App\Models\SolicitudServicio;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class InventarioVentasDetalleSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    private int $rowNumber = 0;

    /**
     * Título de la pestaña dentro del libro de Excel.
     */
    public function title(): string
    {
        return 'Ventas e Inventario';
    }

    /**
     * Obtiene los registros de ventas/compras de inventario.
     */
    public function collection(): Collection
    {
        return SolicitudServicio::with(['user', 'producto'])
            ->where('tipo', 'compra')
            ->get();
    }

    /**
     * Encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            '#',
            'ID Solicitud',
            'Cliente',
            'Producto / Ítem',
            'Cantidad',
            'Precio Unitario ($)',
            'Total ($)',
            'Estado',
            'Fecha',
        ];
    }

    /**
     * Mapea cada fila agregando el número consecutivo (#).
     *
     * @param mixed $solicitud
     * @return array
     */
    public function map($solicitud): array
    {
        $this->rowNumber++;

        $fecha = $solicitud->created_at 
            ? $solicitud->created_at->format('Y-m-d H:i') 
            : 'N/A';

        $cliente = $solicitud->nombre_cliente 
            ?: ($solicitud->user?->name ?? 'N/A');

        $productoNombre = $solicitud->producto?->nombre ?? $solicitud->asunto;
        $precioUnitario = (float) ($solicitud->precio_unitario ?? ($solicitud->producto?->precio ?? 0));

        return [
            $this->rowNumber,
            $solicitud->id,
            $cliente,
            $productoNombre,
            $solicitud->cantidad ?? 1,
            $precioUnitario,
            (float) ($solicitud->valor ?? 0),
            $solicitud->estado ?? 'Resuelto',
            $fecha,
        ];
    }
}