<?php

namespace App\Exports;

use App\Models\ReporteServicio;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteIndividualExport implements FromCollection, WithHeadings, WithMapping
{
    protected $reporteId;

    public function __construct($reporteId)
    {
        $this->reporteId = $reporteId;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return ReporteServicio::with(['solicitudservicio', 'solicitudservicio.user', 'administrador'])
            ->where('id', $this->reporteId)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID Reporte',
            'Cliente',
            'Teléfono',
            'ID Solicitud',
            'Diagnóstico',
            'Trabajo Realizado',
            'Repuestos Utilizados',
            'Mano de Obra ($)',
            'Repuestos ($)',
            'Costo Total ($)',
            'Técnico / Admin',
            'Fecha de Registro',
        ];
    }

    public function map($reporte): array
    {
        $nombreCliente = $reporte->nombre_cliente 
            ?? ($reporte->solicitudservicio && $reporte->solicitudservicio->user ? $reporte->solicitudservicio->user->name : 'Cliente');

        return [
            $reporte->id,
            $nombreCliente,
            $reporte->telefono_cliente ?? 'N/A',
            $reporte->solicitud_servicio_id,
            $reporte->diagnostico,
            $reporte->trabajo_realizado,
            $reporte->repuestos_utilizados ?? 'N/A',
            $reporte->costo_mano_obra ?? 0,
            $reporte->costo_repuestos ?? 0,
            $reporte->costo_total ?? 0,
            $reporte->administrador->name ?? 'Admin',
            $reporte->created_at ? $reporte->created_at->format('Y-m-d H:i') : '',
        ];
    }
}