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

class FacturaServicioMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reporte;

    /**
     * Crear una nueva instancia del mensaje.
     */
    public function __construct(ReporteServicio $reporte)
    {
        $this->reporte = $reporte;
    }

    /**
     * Asunto y remitente del correo.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Factura de Soporte Técnico - Antilow #REQ-{$this->reporte->solicitud_servicio_id}",
        );
    }

    /**
     * Definición del contenido y la vista HTML del correo.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
        );
    }

    /**
     * Adjuntar el PDF generado en vivo.
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.factura', ['reporte' => $this->reporte]);

        return [
            Attachment::fromData(fn () => $pdf->output(), "Factura_Antilow_REQ_{$this->reporte->solicitud_servicio_id}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
