# Plan: Nuevo Proyecto Laravel + Vue 3 — Center Web 3.0

Fecha: 18/06/2026
Autor: Equipo de desarrollo

---

## Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| **Backend** | Laravel 13 (PHP 8.4) |
| **Frontend** | Vue 3 (Composition API) + TypeScript |
| **Rendering** | Inertia.js (SPA-like con Laravel routing) |
| **Estilo** | Tailwind CSS v4 + dark mode |
| **Componentes UI** | shadcn-vue (Reka UI + Tailwind) |
| **Estado** | Pinia |
| **Íconos** | Lucide |
| **Auth** | Laravel Breeze (Inertia Vue stack) |
| **Base de Datos** | MySQL `centerweb` (misma BD actual) |

---

## Alcance Inicial

Módulo **Gestión** (sin módulo Óptica):

1. Login / Logout / Password Reset
2. Sidebar con menú de navegación
3. Dashboard con tarjetas de acceso rápido (shortcuts configurables)
4. Clientes (CRUD + Cuenta Corriente)
5. Productos (CRUD + consulta precios)
6. Proveedores (CRUD)
7. Compras (con items y lotes)
8. Ventas (facturador + formas de pago)
9. Facturas (búsqueda y gestión)
10. Stock (control de stock y ajustes)
11. Caja (movimientos y transferencias)
12. Cierres (cierre de caja, arqueo)
13. Informes (ventas, consolidado, estadísticas)
14. Admin (usuarios, monedas, perfiles)

---

## Diseño Visual

### Principios
- **Rediseño completo**: no hereda Bootstrap 3 ni la estética actual
- **Moderno, limpio y profesional**: colores neutros + acentos vibrantes
- **Sidebar oscuro**: navegación principal colapsable
- **Contenido claro**: área de trabajo con fondo claro/white
- **Responsive**: adaptado a desktop y mobile
- **Dark mode**: soporte nativo (Breeze + Tailwind)

### Paleta de Colores (propuesta inicial)

| Rol | Color |
|-----|-------|
| Sidebar | Slate-900 / Slate-800 |
| Fondo contenido | Slate-50 / White |
| Acento primario | Indigo-600 |
| Acento secundario | Emerald-600 |
| Texto principal | Slate-900 |
| Texto secundario | Slate-500 |

### Layout

```
┌────────────┬──────────────────────────────────┐
│            │                                  │
│  SIDEBAR   │        CONTENIDO PRINCIPAL       │
│            │                                  │
│  ▶ Opciones│   Breadcrumb                     │
│  ▶ Informes│   ─────────────────────           │
│  ▶ Manten. │   Page content here              │
│            │                                  │
│  👤 Usuario│                                  │
│  🏢 Suc: 1 │                                  │
│  🚪 Salir  │                                  │
│            │                                  │
└────────────┴──────────────────────────────────┘
```

---

## Estructura del Proyecto (Nuevo)

```
center-web-vue/              # Directorio nuevo (paralelo a centerweb3)
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/                 # Breeze auth controllers
│   │   ├── HomeController.php    # Dashboard + shortcuts
│   │   ├── ClientesController.php
│   │   ├── ProductosController.php
│   │   ├── ProveedoresController.php
│   │   ├── VentasController.php
│   │   ├── ComprasController.php
│   │   ├── FacturasController.php
│   │   ├── CajasController.php
│   │   ├── CierresController.php
│   │   ├── CtrolStockController.php
│   │   ├── EstadisticasController.php
│   │   ├── MonedasController.php
│   │   ├── MarcasController.php
│   │   ├── AfipController.php
│   │   ├── SucursalesController.php
│   │   ├── ServiciosController.php
│   │   ├── TarjetasController.php
│   │   └── UserController.php
│   ├── Http/Middleware/
│   │   └── Admin.php             # Filtro perfil_id == 'ADM'
│   ├── Models/
│   │   ├── User.php              # + perfil_id, sucursal, home_shortcuts
│   │   ├── Perfil.php
│   │   ├── Sucursal.php
│   │   ├── Cliente.php
│   │   ├── Producto.php
│   │   ├── Proveedor.php
│   │   ├── Factura.php
│   │   ├── Inventario.php
│   │   ├── MovimientoProducto.php (moviproductos)
│   │   ├── Caja.php
│   │   ├── Cierre.php
│   │   ├── Moneda.php
│   │   ├── Cotizacion.php
│   │   ├── Familia.php
│   │   ├── Marca.php
│   │   ├── Comprobante.php
│   │   ├── Mcaja.php
│   │   ├── Mcierre.php
│   │   ├── Minforme.php
│   │   ├── MinformeCod.php
│   │   ├── MinformeTipo.php
│   │   ├── Lote.php
│   │   ├── Correo.php
│   │   ├── HisProducto.php
│   │   ├── AuditoriaCaja.php
│   │   ├── Publicacion.php
│   │   ├── Precio.php
│   │   ├── TarComercio.php
│   │   ├── TarLiquidacion.php
│   │   ├── TarOperacion.php
│   │   ├── TarProducto.php
│   │   └── TarTerminal.php
│   └── helpers.php                # Funciones globales
├── config/
│   └── home.php                   # Shortcuts configurables
├── resources/js/
│   ├── Components/
│   │   ├── ui/                    # shadcn-vue components
│   │   ├── Sidebar.vue
│   │   ├── SidebarItem.vue
│   │   ├── ShortcutCard.vue
│   │   ├── ShortcutConfigModal.vue
│   │   ├── UserDropdown.vue
│   │   └── Carousel.vue
│   ├── Layouts/
│   │   ├── AppLayout.vue          # Sidebar + main content
│   │   └── GuestLayout.vue        # Layout login
│   ├── Pages/
│   │   ├── Dashboard.vue
│   │   ├── Auth/                  # Breeze pages
│   │   ├── Clientes/Index.vue
│   │   ├── Productos/Index.vue
│   │   ├── Productos/ConsultaPrecio.vue
│   │   ├── Proveedores/Index.vue
│   │   ├── Ventas/Altas.vue
│   │   ├── Compras/Index.vue
│   │   ├── Facturas/Index.vue
│   │   ├── Cajas/Movimientos.vue
│   │   ├── Cajas/Transferencias.vue
│   │   ├── Cierres/Index.vue
│   │   ├── Stock/Index.vue
│   │   ├── Estadisticas/Consolidado.vue
│   │   └── Admin/Usuarios.vue
│   ├── lib/
│   │   └── utils.ts               # cn() utility
│   ├── stores/
│   │   └── shortcuts.ts           # Pinia store
│   ├── types/
│   │   └── index.ts               # TypeScript types
│   ├── app.js
│   └── ssr.js
├── routes/
│   └── web.php                    # Mismas rutas de web.php actual
└── package.json
```

---

## Fases de Implementación

### Fase 1 — Proyecto Base + Auth
- `composer create-project laravel/laravel center-web-vue`
- `composer require laravel/breeze --dev`
- `php artisan breeze:install vue --dark`
- Configurar `.env` apuntando a BD `centerweb`
- Modificar login para usar campo `name` en vez de `email`
- Probar registro y login

### Fase 2 — Modelos y Config
- Crear todos los modelos Eloquent para las tablas existentes
- Copiar `config/home.php` del proyecto actual
- Copiar `app/helpers.php` con funciones utilitarias
- Crear middleware `Admin` (filtro por `perfil_id == 'ADM'`)

### Fase 3 — Layout + Sidebar + Navegación
- Personalizar `AppLayout.vue` con sidebar izquierdo colapsable
- Crear menú con 3 secciones: Opciones, Informes, Mantenimiento
- Mostrar/ocultar items según perfil (ADM vs regular)
- Breadcrumb dinámico
- User dropdown con cambiar contraseña y cerrar sesión

### Fase 4 — Dashboard
- Grid de shortcut cards (similar al actual pero con nuevo diseño)
- Modal de configuración con drag & drop
- Carrusel de imágenes
- Guardar shortcuts vía POST

### Fase 5 a 14 — Módulos de Gestión (uno por uno)
Cada módulo sigue el patrón:
1. Ruta en `web.php` (Inertia response)
2. Controller con métodos index, buscar, show, store, update, delete
3. Página Vue con tabla, búsqueda, filtros
4. Modal/Formulario para crear/editar
5. Validaciones

---

## Dependencias npm

```json
{
  "lucide-vue-next": "^0.400",
  "reka-ui": "^2.0",
  "pinia": "^2.1",
  "@vueuse/core": "^11",
  "zod": "^3.23",
  "vue-sonner": "^1.0",
  "@vueuse/integrations": "^11",
  "reka-ui": "^2.0",
  "vaul-vue": "^0.2"
}
```

---

## Cambios Clave vs Proyecto Actual

| Aspecto | Actual (centerweb3) | Nuevo (center-web-vue) |
|---------|--------------------|------------------------|
| Frontend | jQuery + Bootstrap 3 | Vue 3 + Inertia + Tailwind |
| Auth | laravel/ui scaffold | Breeze Inertia Vue |
| Tablas | Bootstrap Table + plugins | shadcn-vue Table (TanStack) |
| Modales | jQuery modals | shadcn Dialog (Reka UI) |
| Íconos | Font Awesome 4 | Lucide |
| CSS | Bootstrap 3 + CSS manual | Tailwind CSS utility-first |
| JS | Scripts inline en Blade | Componentes Vue + TypeScript |
| Drag & drop | jQuery UI Sortable | @vueuse/sortable |
| Dashboard | Blade + jQuery | Vue SFC + Pinia |
| Notificaciones | SweetAlert2 | vue-sonner |

---

## Notas

- La BD no se modifica. Se trabaja con las tablas existentes.
- El login usa el campo `name` (no email), como en el sistema actual.
- El perfil del usuario determina qué menús y opciones se muestran.
- Se respeta la lógica de negocio existente (helpers, validaciones CUIT, etc.).
- Los controladores se adaptan para devolver respuestas Inertia en vez de Blade views.

---

*Fin del plan*
