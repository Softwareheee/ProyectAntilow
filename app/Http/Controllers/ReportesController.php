<?php

namespace App\Http\Controllers;

use App\Mail\FacturaServicioMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ReporteServicio;
use App\Models\SolicitudServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportesController extends Controller
{
    /**
     * Devuelve la lista completa de reportes generados.
     */
    public function index()
    {
        $reportes = ReporteServicio::with([
            'solicitudservicio.user',
            'solicitudservicio.producto',
            'administrador'
        ])->latest()->get();

        return response()->json($reportes);
    }

    /**
     * Guarda el reporte técnico y actualiza el estado de la solicitud de servicio.
     */
   /**
     * Guarda el reporte técnico, actualiza el estado de la solicitud y envía la factura por correo.
     */
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

        $reporte = ReporteServicio::create([
            'solicitud_servicio_id' => $validatedData['solicitud_servicio_id'],
            'admin_id'              => Auth::id(),
            'diagnostico'           => $validatedData['diagnostico'],
            'trabajo_realizado'     => $validatedData['trabajo_realizado'],
            'repuestos_utilizados'  => $validatedData['repuestos_utilizados'] ?? null,
            'costo_mano_obra'       => $validatedData['costo_mano_obra'],
            'costo_repuestos'       => $validatedData['costo_repuestos'],
            'costo_total'           => $costoTotal,
            'estado_atencion'       => 'completado'
        ]);

        // Actualizar el estado y valor en la solicitud original
        $solicitud = SolicitudServicio::find($validatedData['solicitud_servicio_id']);
        if ($solicitud) {
            $solicitud->update([
                'estado' => 'resuelto',
                'valor'  => $costoTotal
            ]);
        }

        // Cargar las relaciones necesarias para la plantilla Blade y la generación del PDF
        $reporte->load(['solicitudservicio.user', 'solicitudservicio.producto', 'administrador']);

        // Enviar notificación por correo electrónico con el PDF al cliente
        if ($reporte->solicitudservicio && $reporte->solicitudservicio->user) {
            Mail::to($reporte->solicitudservicio->user->email)->send(new FacturaServicioMail($reporte));
        }

        return response()->json([
            'message' => 'Reporte registrado y correo enviado exitosamente',
            'reporte' => $reporte
        ], 201);
    }
    /**
     * Muestra el detalle de un reporte individual.
     */
    public function show(int|string $id)
    {
        $reporte = ReporteServicio::with([
            'solicitudservicio.user',
            'solicitudservicio.producto',
            'administrador'
        ])->findOrFail($id);

        return response()->json($reporte);
    }

    public function descargarFactura($id)
{
    $reporte = ReporteServicio::with([
        'solicitudservicio.user',
        'administrador'
    ])->findOrFail($id);

    // Generar el PDF desde la vista Blade
    $pdf = Pdf::loadView('pdf.factura', compact('reporte'));

    return $pdf->download("Factura_Antilow_REQ_{$reporte->solicitud_servicio_id}.pdf");
}

}