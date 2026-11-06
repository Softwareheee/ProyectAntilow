# Antilow | Guía del proyecto

Antilow es una plataforma web para gestionar soporte técnico, inventario de periféricos, solicitudes de compra y reportes de servicio. El workspace reúne dos repositorios Git como submódulos: el cliente web y la API.

## Arquitectura y módulos

- **Frontend (`AntilowFront`)**: React 19, Vite 8, Tailwind CSS 4 y Axios. Contiene la portada pública, registro e inicio de sesión, catálogo, solicitudes de soporte y compra, administración y reportes. Swagger UI se incorpora localmente al bundle.
- **API (`ProyectAntilow`)**: Laravel 13 sobre PHP `^8.3` (PHP 8.3 a 8.x), Laravel Sanctum, MySQL, `barryvdh/laravel-dompdf` y `maatwebsite/excel` 4.x.
- **Persistencia y archivos**: MySQL guarda usuarios, productos, solicitudes, reportes y comentarios de reportes. Las imágenes se guardan en `storage/app/public/productos` y se exponen mediante el enlace `public/storage`.
- **Ejecución local**: `compose.yaml` define la aplicación Laravel y servicios auxiliares para MySQL, Redis, Meilisearch, Mailpit y Selenium.

El frontend envía las peticiones HTTP a la URL configurada en `VITE_API_URL` (base de la API, normalmente terminada en `/api`). El cliente Axios adjunta el token Sanctum a las operaciones autenticadas.

## Funcionalidades y permisos

### Visitantes y clientes

- La portada y el catálogo están disponibles sin iniciar sesión; las cuentas públicas nuevas se registran con el rol `client`.
- Para enviar solicitudes de soporte o compra se requiere una cuenta autenticada. El cliente puede consultar su historial de solicitudes y los reportes asociados a sus solicitudes.
- Una solicitud de compra guarda el contacto del cliente, cantidad y precio unitario de referencia. Se valida que el producto esté activo y que la cantidad no exceda el stock disponible. Crear la solicitud no descuenta ni reserva unidades del inventario.
- En Soporte técnico se puede crear y filtrar el historial de solicitudes por estado. El cliente recibe confirmación visual tras el envío.

### Administradores

- La navegación administrativa incluye gestión de usuarios, clientes y solicitudes, además del módulo de reportes.
- El administrador puede gestionar productos (incluidas imágenes), editar el estado/prioridad y notas de solicitudes, y generar informes de servicio.
- Al guardar un informe, la API suma mano de obra y repuestos, registra los datos de contacto y cambia la solicitud vinculada a estado resuelto.
- El panel de Reportes permite descargar facturas PDF de servicio, proformas de compra y exportaciones contables o de reportes en Excel/CSV.
- En la API, las operaciones de gestión de usuarios, clientes, productos, solicitudes y creación de informes requieren autenticación Sanctum y `role=admin`. La lista y consulta de reportes aplica el filtro de propiedad para clientes. Las rutas de exportación requieren token Sanctum; la interfaz de reportes se muestra solo a administradores.

## API principal

Las rutas siguientes se expresan relativas al prefijo `/api`:

| Operación | Ruta | Acceso |
|---|---|---|
| Registro / inicio de sesión | `POST /register`, `POST /login` | Público |
| Catálogo | `GET /productos` | Público; el administrador también ve productos inactivos |
| Perfil / cerrar sesión | `GET /user`, `POST /logout` | Token Sanctum |
| Solicitudes | `GET /solicitudes-servicio`, `POST /solicitudes-servicio` | Token; clientes ven las propias |
| Administrar solicitudes | `PATCH` o `DELETE /solicitudes-servicio/{solicitudServicio}` | Token y rol admin |
| Reportes | `GET /reportes`, `GET /reportes/{id}` | Token; clientes solo consultan los propios |
| Crear reporte / comentario | `POST /reportes`, `POST /reportes/comentarios` | Crear reporte: admin; comentario: token |
| Factura de servicio | `GET /reportes/{id}/factura/descargar` | Token |
| Proforma de compra | `GET /solicitudes-servicio/{solicitudServicio}/factura-compra/descargar` | Token y rol admin |
| Exportar balance general | `GET /reportes/exportar/excel`, `GET /reportes/exportar/csv` | Token Sanctum |
| Exportar reporte individual | `GET /reportes/{id}/exportar/excel`, `GET /reportes/{id}/exportar/csv` | Token Sanctum |
| Usuarios y clientes | `/usuarios`, `/clientes` (operaciones CRUD habilitadas) | Token y rol admin |
| Gestión de productos | `POST /productos`, `PUT`, `PATCH` o `DELETE /productos/{producto}` | Token y rol admin |
| Especificación OpenAPI | `GET /openapi.json` | Público |

Las rutas de consulta, descarga y exportación requieren un token Sanctum aunque la interfaz de Reportes solo se muestre a administradores. En Swagger, reemplaza `{id}`, `{producto}`, `{usuario}`, `{cliente}` o `{solicitudServicio}` por el identificador real del recurso.

## Solicitudes, facturas y correo

- Los informes técnicos se muestran como facturas PDF y usan la vista `resources/views/pdf/factura.blade.php`.
- La descarga asociada a una solicitud de compra genera una **proforma**, mediante `resources/views/pdf/factura_compra.blade.php`. Incluye los datos de producto y compra; no acredita el pago ni la realización de una venta.
- Al crear un informe técnico, la API intenta enviar su factura al correo del usuario asociado. `MAIL_MAILER=log` (valor de `.env.example`) registra el mensaje localmente en vez de enviarlo por SMTP. Para envío real, configura un transport de correo operativo en el entorno.
- `barryvdh/laravel-dompdf` está fijado en `composer.lock` (wrapper v3.1.2; dompdf v3.1.6). `composer install` instala las dependencias y realiza el descubrimiento de paquetes de Laravel.

## Exportaciones Excel y CSV

El módulo incorpora exportaciones generales y por informe técnico:

| Contenido | Ruta | Archivo descargado |
|---|---|---|
| Balance general Excel | `GET /api/reportes/exportar/excel` | `reporte_contable_general.xlsx` |
| Balance general CSV | `GET /api/reportes/exportar/csv` | `reporte_contable_general.csv` |
| Informe técnico individual Excel | `GET /api/reportes/{id}/exportar/excel` | `reporte_{id}.xlsx` |
| Informe técnico individual CSV | `GET /api/reportes/{id}/exportar/csv` | `reporte_{id}.csv` |

El libro Excel general contiene tres hojas: **Balance General** (métricas e ingresos por servicio y venta de inventario), **Mantenimientos** y **Ventas e Inventario**. La exportación individual contiene los datos del informe, cliente, trabajo realizado, costos y fecha. La dependencia `maatwebsite/excel` se declara en `composer.json`; sus clases de exportación se encuentran en `app/Exports`.

## Swagger / OpenAPI

Con el frontend y el backend activos, abre [Swagger UI](http://localhost:5173/api-docs) (si Vite usa otro puerto, conserva la ruta `/api-docs` y reemplaza el puerto). La página importa Swagger UI y sus estilos desde las dependencias instaladas, sin CDN.

La especificación fuente está en `ProyectAntilow/public/openapi.json` y, en desarrollo local, Laravel la sirve en [http://localhost:8000/api/openapi.json](http://localhost:8000/api/openapi.json). Swagger UI obtiene la URL de la API desde `VITE_API_URL` y la usa tanto para cargar la especificación como para ejecutar las llamadas **Try it out**; el valor debe apuntar al backend y terminar en `/api` (por ejemplo, `http://localhost:8000/api`). Los métodos y rutas mostrados se combinan con esa URL base.

Si frontend y API están en hosts u orígenes distintos, agrega el origen exacto del frontend a `CORS_ALLOWED_ORIGINS` en el `.env` del backend. El ejemplo incluye `localhost:5173`, `localhost:5174` y `localhost:5175`; si Vite utiliza otro puerto, también debe permitirse.

Para probar operaciones protegidas:

1. Ejecuta `POST /api/login` o `POST /api/register`.
2. Copia el campo `token` de la respuesta.
3. En Swagger UI pulsa **Authorize** y pega el token sin anteponer `Bearer`.
4. Ejecuta la operación protegida con **Try it out**. En las operaciones de descarga, Swagger recibe el archivo PDF, Excel o CSV definido en la respuesta.
5. Las operaciones marcadas como administrativas requieren además una cuenta cuyo rol sea `admin`. Las operaciones de gestión del catálogo, usuarios y solicitudes están restringidas; las consultas y exportaciones de reportes solo exigen token en la API, aunque la interfaz web de Reportes es administrativa.

La especificación documenta autenticación, productos, solicitudes de soporte y compra, usuarios, clientes, reportes, comentarios, PDFs, exportaciones y el endpoint que sirve el propio documento. Incluye modelos de entrada/salida, restricciones de campos, códigos de respuesta y los parámetros de ruta; los detalles de cada operación se consultan al desplegarla en Swagger UI.

## Desarrollo local

Desde la raíz del workspace, crea la configuración local del backend y levanta los servicios:

```bash
cd ProyectAntilow
cp .env.example .env
docker compose up -d
docker compose exec laravel.test composer install --no-interaction
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate
```

Para crear el primer administrador, define `ADMIN_NAME`, `ADMIN_EMAIL` y `ADMIN_PASSWORD` en el `.env` del backend antes de ejecutar el seeder. El correo debe ser válido y la contraseña debe tener al menos 16 caracteres. El seeder no crea credenciales predeterminadas.

```bash
docker compose exec laravel.test php artisan db:seed
docker compose exec laravel.test php artisan storage:link
```

En otra terminal, configura e inicia el frontend:

```bash
cd AntilowFront
cp .env.example .env
npm ci
npm run dev
```

`AntilowFront/.env.example` configura `VITE_API_URL=http://localhost:8000/api`; el puerto del frontend puede variar si Vite selecciona otro puerto disponible. `APP_PORT` en el `.env` del backend controla el puerto HTTP de Laravel (8000 por defecto en el ejemplo).

## Configuración de despliegue

- Configura `APP_URL`, una `APP_KEY` propia, credenciales de base de datos y demás secretos solo en el entorno de despliegue. No publiques `.env` ni secretos en el repositorio.
- Define `ADMIN_NAME`, `ADMIN_EMAIL` y `ADMIN_PASSWORD` (mínimo 16 caracteres) antes de ejecutar `php artisan db:seed` para crear el administrador inicial.
- Ajusta `CORS_ALLOWED_ORIGINS` con los orígenes reales del frontend, separados por comas. El ejemplo contiene orígenes localhost para desarrollo.
- Establece `VITE_API_URL` con la URL pública del backend terminada en `/api` antes de construir el frontend.
- Usa un servicio de correo real para entregar facturas; `MAIL_MAILER=log` es el valor de desarrollo.
- Conserva accesibles los archivos de imagen del disco público y crea el enlace de almacenamiento con `php artisan storage:link`.

## Validación de cambios

Desde `AntilowFront`, los comandos disponibles son:

```bash
npm run lint
npm run build
```

Desde `ProyectAntilow` (o dentro del servicio Sail):

```bash
php artisan test
```

Los cambios de esquema de base de datos se aplican con `php artisan migrate`. Las migraciones de los flujos principales incluyen la creación de productos, solicitudes, reportes de servicio y comentarios, además de contacto, cantidad y precio de referencia:

- `2026_09_03_000002_create_solicitudes_servicio_table`
- `2026_09_10_000001_create_productos_table`
- `2026_09_24_202814_create_reportes_servicio_table`
- `2026_10_08_000001_add_customer_contact_to_reportes_servicio_table`
- `2026_10_08_000002_add_customer_contact_to_solicitudes_servicio_table`
- `2026_10_09_000253_create_comentario_reportes_table`
- `2026_10_15_000001_add_quantity_to_solicitudes_servicio_table`
- `2026_10_15_000002_add_price_snapshot_to_solicitudes_servicio_table`
