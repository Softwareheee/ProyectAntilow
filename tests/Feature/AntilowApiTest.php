<?php

namespace Tests\Feature;

use App\Mail\FacturaServicioMail;
use App\Models\Producto;
use App\Models\ReporteServicio;
use App\Models\SolicitudServicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AntilowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_register_as_a_client_and_receive_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Cliente Prueba',
            'email' => 'cliente@example.test',
            'password' => 'password-segura',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.role', 'client')
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'role'], 'token'])
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', [
            'email' => 'cliente@example.test',
            'role' => 'client',
        ]);
    }

    public function test_public_catalog_hides_inactive_products_and_admin_can_see_them(): void
    {
        $active = $this->createProduct(['nombre' => 'Teclado activo']);
        $inactive = $this->createProduct(['nombre' => 'Mouse archivado', 'activo' => false]);

        $this->getJson('/api/productos')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $active->id);

        Sanctum::actingAs($this->createUser('admin'));

        $this->getJson('/api/productos')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $inactive->id, 'nombre' => 'Mouse archivado']);
    }

    public function test_client_can_create_a_purchase_request_with_price_snapshot_without_changing_stock(): void
    {
        $client = $this->createUser();
        $product = $this->createProduct(['precio' => 129.95, 'stock' => 5]);
        Sanctum::actingAs($client);

        $this->postJson('/api/solicitudes-servicio', [
            'tipo' => 'soporte',
            'asunto' => 'No importa: se completa con el producto',
            'descripcion' => 'Solicitud original',
            'nombre_cliente' => 'Cliente de prueba',
            'telefono_cliente' => '5551234',
            'product_id' => $product->id,
            'cantidad' => 2,
        ])
            ->assertCreated()
            ->assertJsonPath('tipo', 'compra')
            ->assertJsonPath('cantidad', 2)
            ->assertJsonPath('precio_unitario', '129.95')
            ->assertJsonPath('asunto', 'Solicitud de compra: ' . $product->nombre);

        $this->assertDatabaseHas('solicitudes_servicio', [
            'user_id' => $client->id,
            'product_id' => $product->id,
            'cantidad' => 2,
            'precio_unitario' => '129.95',
        ]);
        $this->assertDatabaseHas('productos', ['id' => $product->id, 'stock' => 5]);
    }

    public function test_purchase_request_is_rejected_when_quantity_exceeds_stock(): void
    {
        $client = $this->createUser();
        $product = $this->createProduct(['stock' => 1]);
        Sanctum::actingAs($client);

        $this->postJson('/api/solicitudes-servicio', [
            'tipo' => 'compra',
            'asunto' => 'Compra',
            'descripcion' => 'Solicitud de compra',
            'nombre_cliente' => 'Cliente de prueba',
            'telefono_cliente' => '5551234',
            'product_id' => $product->id,
            'cantidad' => 2,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('solicitudes_servicio', 0);
        $this->assertDatabaseHas('productos', ['id' => $product->id, 'stock' => 1]);
    }

    public function test_client_can_only_list_own_service_requests(): void
    {
        $client = $this->createUser();
        $otherClient = $this->createUser();
        $ownRequest = $this->createRequest($client);
        $this->createRequest($otherClient);
        Sanctum::actingAs($client);

        $this->getJson('/api/solicitudes-servicio')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $ownRequest->id);
    }

    public function test_admin_creating_a_service_report_completes_request_and_emails_invoice(): void
    {
        Mail::fake();
        $client = $this->createUser();
        $admin = $this->createUser('admin');
        $request = $this->createRequest($client);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/reportes', [
            'solicitud_servicio_id' => $request->id,
            'diagnostico' => 'Falla en el ventilador',
            'trabajo_realizado' => 'Limpieza y reemplazo',
            'repuestos_utilizados' => 'Ventilador',
            'costo_mano_obra' => 25.50,
            'costo_repuestos' => 14.50,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('reporte.costo_total', 40);

        $reportId = $response->json('reporte.id');
        $this->assertDatabaseHas('reportes_servicio', [
            'id' => $reportId,
            'solicitud_servicio_id' => $request->id,
            'admin_id' => $admin->id,
            'costo_total' => '40.00',
        ]);
        $this->assertDatabaseHas('solicitudes_servicio', [
            'id' => $request->id,
            'estado' => 'resuelto',
            'valor' => '40.00',
        ]);
        Mail::assertSent(FacturaServicioMail::class, fn (FacturaServicioMail $mail): bool => $mail->reporte->id === $reportId);
    }

    public function test_client_cannot_use_admin_only_request_management(): void
    {
        $client = $this->createUser();
        $request = $this->createRequest($client);
        Sanctum::actingAs($client);

        $this->patchJson("/api/solicitudes-servicio/{$request->id}", [
            'estado' => 'resuelta',
        ])->assertForbidden();

        $this->assertDatabaseHas('solicitudes_servicio', [
            'id' => $request->id,
            'estado' => 'pendiente',
        ]);
    }

    public function test_authenticated_client_can_add_a_valid_report_comment(): void
    {
        $client = $this->createUser();
        $admin = $this->createUser('admin');
        $request = $this->createRequest($client);
        $report = ReporteServicio::create([
            'solicitud_servicio_id' => $request->id,
            'admin_id' => $admin->id,
            'diagnostico' => 'Diagnóstico',
            'trabajo_realizado' => 'Trabajo realizado',
            'costo_mano_obra' => 10,
            'costo_repuestos' => 0,
            'costo_total' => 10,
            'estado_atencion' => 'completado',
        ]);
        Sanctum::actingAs($client);

        $this->postJson('/api/reportes/comentarios', [
            'reporte_servicio_id' => $report->id,
            'tipo' => 'duda',
            'mensaje' => '¿Qué cuidados recomiendan?',
        ])
            ->assertCreated()
            ->assertJsonPath('data.reporte_servicio_id', $report->id)
            ->assertJsonPath('data.user_id', $client->id);

        $this->assertDatabaseHas('comentario_reportes', [
            'reporte_servicio_id' => $report->id,
            'user_id' => $client->id,
            'tipo' => 'duda',
        ]);
    }

    public function test_report_comment_rejects_an_unsupported_type(): void
    {
        $client = $this->createUser();
        $admin = $this->createUser('admin');
        $request = $this->createRequest($client);
        $report = ReporteServicio::create([
            'solicitud_servicio_id' => $request->id,
            'admin_id' => $admin->id,
            'diagnostico' => 'Diagnóstico',
            'trabajo_realizado' => 'Trabajo realizado',
            'costo_mano_obra' => 10,
            'costo_repuestos' => 0,
            'costo_total' => 10,
            'estado_atencion' => 'completado',
        ]);
        Sanctum::actingAs($client);

        $this->postJson('/api/reportes/comentarios', [
            'reporte_servicio_id' => $report->id,
            'tipo' => 'otro',
            'mensaje' => 'Comentario de prueba',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('comentario_reportes', 0);
    }

    public function test_admin_can_download_general_excel_and_csv_exports(): void
    {
        Sanctum::actingAs($this->createUser('admin'));

        $this->get('/api/reportes/exportar/excel')
            ->assertOk()
            ->assertDownload('reporte_contable_general.xlsx');

        $this->get('/api/reportes/exportar/csv')
            ->assertOk()
            ->assertDownload('reporte_contable_general.csv');
    }

    public function test_client_can_download_own_report_as_excel_csv_and_pdf(): void
    {
        $client = $this->createUser();
        $report = $this->createReport($client);
        Sanctum::actingAs($client);

        $this->get("/api/reportes/{$report->id}/exportar/excel")
            ->assertOk()
            ->assertDownload("reporte_{$report->id}.xlsx");

        $this->get("/api/reportes/{$report->id}/exportar/csv")
            ->assertOk()
            ->assertDownload("reporte_{$report->id}.csv");

        $this->get("/api/reportes/{$report->id}/factura/descargar")
            ->assertOk()
            ->assertDownload("Factura_Antilow_REQ_{$report->solicitud_servicio_id}.pdf");
    }

    private function createUser(string $role = 'client'): User
    {
        return User::create([
            'name' => ucfirst($role) . ' de prueba',
            'email' => uniqid($role . '-', true) . '@example.test',
            'password' => 'password-segura',
            'role' => $role,
        ]);
    }

    private function createProduct(array $attributes = []): Producto
    {
        return Producto::create(array_merge([
            'nombre' => 'Producto de prueba',
            'categoria' => 'periferico',
            'precio' => 49.99,
            'stock' => 5,
            'activo' => true,
        ], $attributes));
    }

    private function createRequest(User $client): SolicitudServicio
    {
        return SolicitudServicio::create([
            'user_id' => $client->id,
            'nombre_cliente' => $client->name,
            'telefono_cliente' => '5551234',
            'tipo' => 'diagnóstico de equipo',
            'asunto' => 'Equipo con fallas',
            'descripcion' => 'La computadora se apaga.',
            'estado' => 'pendiente',
            'prioridad' => 'normal',
        ]);
    }

    private function createReport(User $client): ReporteServicio
    {
        $request = $this->createRequest($client);
        $admin = $this->createUser('admin');

        return ReporteServicio::create([
            'solicitud_servicio_id' => $request->id,
            'admin_id' => $admin->id,
            'nombre_cliente' => $client->name,
            'telefono_cliente' => '5551234',
            'diagnostico' => 'Diagnóstico de prueba',
            'trabajo_realizado' => 'Trabajo realizado',
            'repuestos_utilizados' => 'Ninguno',
            'costo_mano_obra' => 10,
            'costo_repuestos' => 5,
            'costo_total' => 15,
            'estado_atencion' => 'completado',
        ]);
    }
}
