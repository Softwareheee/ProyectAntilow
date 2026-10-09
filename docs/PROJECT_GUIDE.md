# Antilow | Guía del proyecto

Antilow es una plataforma de soporte técnico y venta de periféricos. El workspace separa el cliente web y la API Laravel.

## Módulos

- **Frontend (`AntilowFront`)**: React + Vite + Tailwind CSS. Incluye autenticación, catálogo público de inventario, solicitudes de soporte/compra, reportes y panel administrativo.
- **API (`ProyectAntilow`)**: Laravel 13, Sanctum y MySQL. Expone autenticación, productos, solicitudes, clientes, administración de usuarios y reportes.
- **Persistencia**: MySQL conserva cuentas, productos, solicitudes y reportes. Las imágenes de productos se almacenan en `storage/app/public/productos`.

## Swagger / OpenAPI

Con Vite y Laravel activos, abre `/api-docs` en el puerto de Vite (por ejemplo, `http://localhost:5173/api-docs`). La interfaz usa Swagger UI instalado localmente. La especificación OpenAPI está en `ProyectAntilow/public/openapi.json`; Laravel la entrega también por `GET /api/openapi.json` para habilitar CORS en desarrollo.

Swagger UI y sus estilos se empaquetan con Vite, por lo que no dependen de un CDN. Se necesita que el backend esté activo y que `VITE_API_URL` apunte a él para cargar la especificación y ejecutar operaciones.

Para probar operaciones autenticadas, inicia sesión mediante `POST /api/login`, copia `token` y úsalo en **Authorize** como Bearer token. Las operaciones marcadas como administrativas también requieren `role=admin`.

## Desarrollo local

El backend se inicia desde `ProyectAntilow` con Docker Compose. Cada colaborador debe crear su `.env` desde `.env.example`, instalar dependencias, ejecutar migraciones y `php artisan storage:link`. El frontend se instala con `npm ci` y configura `VITE_API_URL` en su `.env`.

Consulta los README de cada subproyecto para los comandos concretos de instalación.
