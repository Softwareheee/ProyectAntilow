<?php

namespace App\Exports\Sheets;

use App\Models\ReporteServicio;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MantenimientoDetalleSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    private int $rowNumber = 0;

    public function title(): string
    {
        return 'Mantenimientos';
    }

    public function collection(): Collection
    {
        return ReporteServicio::with(['solicitudservicio.user'])->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'ID Reporte',
            'N° Solicitud',
            'Cliente',
            'Teléfono',
            'Diagnóstico',
            'Trabajo Realizado',
            'Repuestos / Piezas',
            'Mano de Obra ($)',
            'Repuestos ($)',
            'Total ($)',
            'Fecha',
        ];
    }

    public function map($reporte): array
    {
        $this->rowNumber++;

        $fecha = $reporte->created_at 
            ? $reporte->created_at->format('Y-m-d H:i') 
            : 'N/A';

        $cliente = $reporte->nombre_cliente 
            ?: ($reporte->solicitudservicio?->user?->name ?? 'N/A');

        return [
            $this->rowNumber,
            $reporte->id,
            $reporte->solicitud_servicio_id,
            $cliente,
            $reporte->telefono_cliente ?? 'N/A',
            $reporte->diagnostico ?? 'N/A',
            $reporte->trabajo_realizado ?? 'N/A',
            $reporte->repuestos_utilizados ?? 'Ninguno',
            (float) ($reporte->costo_mano_obra ?? 0),
            (float) ($reporte->costo_repuestos ?? 0),
            (float) ($reporte->costo_total ?? 0),
            $fecha,
        ];
    }
}