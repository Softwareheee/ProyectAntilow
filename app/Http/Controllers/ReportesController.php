<?php

namespace App\Http\Controllers;

use App\Mail\FacturaServicioMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ReporteServicio;
use App\Models\SolicitudServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Genera informes técnicos, actualiza casos y entrega facturas PDF. */
class ReportesController extends Controller
{
    /** Lista reportes con cliente, solicitud, producto y administrador relacionados. */
    public function index(Request $request)
    {
        $query = ReporteServicio::with([
            'solicitudservicio.user',
            'solicitudservicio.producto',
            'administrador'
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

        // La solicitud conserva el resultado y el total que se facturará.
        $solicitud->update([
            'estado' => 'resuelto',
            'valor'  => $costoTotal
        ]);

        // La plantilla de correo y el PDF necesitan los datos relacionados.
        $reporte->load(['solicitudservicio.user', 'solicitudservicio.producto', 'administrador']);

        // Si la solicitud pertenece a una cuenta, envía la factura a su correo.
        if ($reporte->solicitudservicio && $reporte->solicitudservicio->user) {
            Mail::to($reporte->solicitudservicio->user->email)->send(new FacturaServicioMail($reporte));
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
            'administrador'
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

        $pdf = Pdf::loadView('pdf.factura', compact('reporte'));

        return $pdf->download("Factura_Antilow_REQ_{$reporte->solicitud_servicio_id}.pdf");
    }

}