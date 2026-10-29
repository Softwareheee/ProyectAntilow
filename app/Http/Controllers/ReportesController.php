<?php

namespace App\Http\Controllers;

use App\Exports\ReporteGeneralExport;
use App\Exports\ReporteIndividualExport;
use App\Mail\FacturaServicioMail;
use App\Models\ComentarioReporte;
use App\Models\ReporteServicio;
use App\Models\SolicitudServicio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

/** Genera informes técnicos, actualiza casos, gestiona seguimientos y entrega facturas PDF, Excel y CSV. */
class ReportesController extends Controller
{
    /** Lista reportes con cliente, solicitud, producto, administrador y comentarios relacionados. */
    public function index(Request $request)
    {
        $query = ReporteServicio::with([
            'solicitudservicio.user',
            'solicitudservicio.producto',
            'administrador',
            'comentarios.user'
        ])->latest();

        if ($request->user()->role !== 'admin') {
            $query->whereHas('solicitudservicio', fn ($solicitudQuery) => $solicitudQuery->where('user_id', $request->user()->id));
        }

        return response()->json($query->get());
    }

    /** Crea el informe, cierra la solicitud y envía la factura al cliente. */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'solicitud_servicio_id' => 'required|exists:solicitudes_servicio,id',
            'diagnostico'          => 'required|string',
            'trabajo_realizado'    => 'required|string',
            'repuestos_utilizados' => 'nullable|string',
            'costo_mano_obra'      => 'required|numeric|min:0',
            'costo_repuestos'      => 'required|numeric|min:0',
        ]);

        $costoTotal = $validatedData['costo_mano_obra'] + $validatedData['costo_repuestos'];
        $solicitud = SolicitudServicio::with('user')->findOrFail($validatedData['solicitud_servicio_id']);

        $reporte = ReporteServicio::create([
            'solicitud_servicio_id' => $validatedData['solicitud_servicio_id'],
            'admin_id'              => Auth::id(),
            'nombre_cliente'        => $solicitud->nombre_cliente ?: $solicitud->user?->name,
            'telefono_cliente'      => $solicitud->telefono_cliente,
            'diagnostico'           => $validatedData['diagnostico'],
            'trabajo_realizado'     => $validatedData['trabajo_realizado'],
            'repuestos_utilizados'  => $validatedData['repuestos_utilizados'] ?? null,
            'costo_mano_obra'       => $validatedData['costo_mano_obra'],
            'costo_repuestos'       => $validatedData['costo_repuestos'],
            'costo_total'           => $costoTotal,
            'estado_atencion'       => 'completado'
        ]);

        $solicitud->update([
            'estado' => 'resuelto',
            'valor'  => $costoTotal
        ]);

        $reporte->load(['solicitudservicio.user', 'solicitudservicio.producto', 'administrador', 'comentarios.user']);

        if ($reporte->solicitudservicio && $reporte->solicitudservicio->user) {
            try {
                Mail::to($reporte->solicitudservicio->user->email)->send(new FacturaServicioMail($reporte));
            } catch (\Exception $e) {
                // Silenciar fallo de SMTP local
            }
        }

        return response()->json([
            'message' => 'Reporte registrado y correo enviado exitosamente',
            'reporte' => $reporte
        ], 201);
    }

    /** Devuelve un informe y sus relaciones por identificador. */
    public function show(Request $request, int|string $id)
    {
        $query = ReporteServicio::with([
            'solicitudservicio.user',
            'solicitudservicio.producto',
            'administrador',
            'comentarios.user'
        ]);

        if ($request->user()->role !== 'admin') {
            $query->whereHas('solicitudservicio', fn ($solicitudQuery) => $solicitudQuery->where('user_id', $request->user()->id));
        }

        $reporte = $query->findOrFail($id);

        return response()->json($reporte);
    }

    /** Renderiza la vista de factura y responde con un archivo PDF descargable. */
    public function descargarFactura(Request $request, $id)
    {
        $query = ReporteServicio::with([
            'solicitudservicio.user',
            'administrador'
        ]);

        if ($request->user()->role !== 'admin') {
            $query->whereHas('solicitudservicio', fn ($solicitudQuery) => $solicitudQuery->where('user_id', $request->user()->id));
        }
        $reporte = $query->findOrFail($id);

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = Pdf::loadView('pdf.factura', compact('reporte'));

        return $pdf->download("Factura_Antilow_REQ_{$reporte->solicitud_servicio_id}.pdf");
    }

    /** Genera el detalle PDF de una solicitud de compra, separado de la factura técnica. */
    public function descargarFacturaCompra(SolicitudServicio $solicitudServicio)
    {
        abort_unless($solicitudServicio->tipo === 'compra', 404);

        $solicitud = $solicitudServicio->load(['user:id,name,email', 'producto:id,nombre,precio']);

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = Pdf::loadView('pdf.factura_compra', compact('solicitud'));

        return $pdf->download("Proforma_Compra_Antilow_{$solicitud->id}.pdf");
    }

    /** Guarda las observaciones / seguimientos de un reporte técnico. */
    public function guardarComentario(Request $request)
    {
        $validated = $request->validate([
            'reporte_servicio_id' => 'required|exists:reportes_servicio,id',
            'tipo'                => 'required|in:felicitacion,falla_post_mantenimiento,duda,sugerencia',
            'mensaje'             => 'required|string|max:1000',
        ]);

        $comentario = ComentarioReporte::create([
            'reporte_servicio_id' => $validated['reporte_servicio_id'],
            'user_id'             => Auth::id(),
            'tipo'                => $validated['tipo'],
            'mensaje'             => $validated['mensaje'],
        ]);

        return response()->json([
            'message' => 'Seguimiento registrado con éxito para el reporte.',
            'data'    => $comentario
        ], 201);
    }

    /** Exporta el reporte contable general en formato Excel (.xlsx). */
    public function exportarExcelGeneral()
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        return (new ReporteGeneralExport)->download('reporte_contable_general.xlsx');
    }

    /** Exporta el reporte contable general en formato CSV (.csv). */
    public function exportarCsvGeneral()
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        return (new ReporteGeneralExport)->download(
            'reporte_contable_general.csv',
            \Maatwebsite\Excel\Excel::CSV,
            ['Content-Type' => 'text/csv']
        );
    }

    /** Exporta un reporte técnico individual por ID en formato Excel (.xlsx). */
    public function downloadExcel($id)
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        return Excel::download(
            new ReporteIndividualExport($id),
            "reporte_{$id}.xlsx"
        );
    }

    /** Exporta un reporte técnico individual por ID en formato CSV (.csv). */
    public function downloadCsv($id)
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        return Excel::download(
            new ReporteIndividualExport($id),
            "reporte_{$id}.csv",
            \Maatwebsite\Excel\Excel::CSV,
            ['Content-Type' => 'text/csv']
        );
    }
}