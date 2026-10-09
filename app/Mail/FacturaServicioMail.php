<?php

namespace App\Mail;

use App\Models\ReporteServicio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Correo de cierre de servicio que adjunta la factura PDF del informe. */
class FacturaServicioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reporte;

    /** Recibe el informe cuyos datos alimentan el correo y el adjunto. */
    public function __construct(ReporteServicio $reporte)
    {
        $this->reporte = $reporte;
    }

    /** Define el asunto del mensaje con el número de solicitud atendida. */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Factura de Soporte Técnico - Antilow #REQ-{$this->reporte->solicitud_servicio_id}",
        );
    }

    /** Selecciona la plantilla HTML que presenta el resumen al cliente. */
    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
        );
    }

    /** Genera y adjunta la factura PDF sin guardarla como archivo temporal. */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.factura', ['reporte' => $this->reporte]);

        return [
            Attachment::fromData(fn () => $pdf->output(), "Factura_Antilow_REQ_{$this->reporte->solicitud_servicio_id}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
