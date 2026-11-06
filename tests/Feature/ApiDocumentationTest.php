<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_openapi_specification_is_available_and_documents_report_operations(): void
    {
        $this->getJson('/api/openapi.json')
            ->assertOk()
            ->assertJsonPath('openapi', '3.0.3')
            ->assertJsonStructure([
                'paths' => [
                    '/reportes/comentarios' => ['post'],
                    '/reportes/exportar/excel' => ['get'],
                    '/reportes/exportar/csv' => ['get'],
                    '/reportes/{id}/exportar/excel' => ['get'],
                    '/reportes/{id}/exportar/csv' => ['get'],
                ],
            ]);
    }
}
