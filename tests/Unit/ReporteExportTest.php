<?php

namespace Tests\Unit;

use App\Exports\ReporteGeneralExport;
use App\Exports\ReporteIndividualExport;
use PHPUnit\Framework\TestCase;

class ReporteExportTest extends TestCase
{
    public function test_general_accounting_export_contains_all_three_sheets(): void
    {
        $sheetTitles = array_map(
            static fn ($sheet): string => $sheet->title(),
            (new ReporteGeneralExport())->sheets(),
        );

        $this->assertSame([
            'Balance General',
            'Mantenimientos',
            'Ventas e Inventario',
        ], $sheetTitles);
    }

    public function test_individual_service_report_export_defines_expected_columns(): void
    {
        $export = new ReporteIndividualExport(1);

        $this->assertSame([
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
        ], $export->headings());
    }
}
