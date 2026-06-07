# Plan de mejora UI - Módulo Tarjetas

## Skills utilizados

- `frontend-design` (anthropics/skills) — diseño visual y estética
- `ui-ux-pro-max` (nextlevelbuilder) — principios UX/UI profesionales
- `redesign-existing-projects` (leonxlnx/taste-skill) — rediseño de proyectos legacy

---

## Paso 1: Analizar el estado actual

Antes de tocar código:
1. Leer los archivos Blade del módulo
2. Identificar el framework CSS (Bootstrap 3, Bootstrap Table, jQuery)
3. Identificar qué librerías JS ya están disponibles (SweetAlert2, Select2, DateRangePicker)
4. Revisar el template base (`template/informes.blade.php` y `template/main.blade.php`)

## Paso 2: Aplicar sistema de diseño vía CSS

Estrategia: **no reemplazar Bootstrap 3**, sino sobrescribir selectores clave con CSS moderno.

### Patrón usado en `index_operaciones.blade.php`:

```css
:root {
    --primary: #2563eb;
    --success: #059669;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --border: #e2e8f0;
    --shadow-md: 0 4px 6px -1px rgba(0,0,0,.07);
    --radius-lg: 12px;
}
```

### Sobrescritura de componentes Bootstrap 3:

| Componente | Qué se cambió |
|------------|--------------|
| `.panel` | border-radius, box-shadow, border-color |
| `.panel-heading` | gradient background, padding, tipografía |
| `.form-control` | border-radius 6px, focus ring sutil |
| `.btn-primary` | gradient, shadow, hover con translateY |
| `.btn-default` | border sutil, hover con color primary |
| `.modal-content` | sin border, shadow-lg, overflow hidden |
| `.modal-header` | gradient oscuro, línea acento verde |
| `.well` | sin border, sin shadow, bg suave |

## Paso 3: Mejorar la estructura HTML

1. **Filtros**: Cambiar de `form-inline` desordenado a un layout flexbox con wrap
   - Cada campo en un contenedor con `min-width` y `flex: 1 1 ...`
   - Labels arriba con uppercase + letter-spacing
   - Botón de búsqueda alineado al fondo con `align-self: flex-end`

2. **Tabla**: Mantener todos los `data-*` attributes de Bootstrap Table intactos
   - Solo mejorar visualmente con CSS (header uppercase, footer con bg, hover)

3. **Modales**: Remover `style` inline de headers (ya no necesarios)
   - Mantener todas las IDs y clases que usa el JavaScript

## Paso 4: Reglas para no romper nada

- **NO tocar JavaScript** — toda la lógica, event handlers, AJAX deben quedar igual
- **NO cambiar IDs** — el JS depende de IDs como `#detIdLiquidacion`, `#cajaCuerpo`, etc.
- **NO cambiar clases funcionales** — `.btn-detalle-ope`, `.btn-caja-ope`, `.btn-asociar-caja`
- **NO cambiar estructura de la tabla** — los `data-field`, `data-formatter` son críticos
- **NO cambiar rutas** — `buscar_operaciones`, `buscar_caja`, `asociar_caja`

## Próximos pasos (views pendientes)

### `index.blade.php` (Carga de archivos)
- Rediseñar drop zone con animaciones y mejor feedback visual
- Barra de progreso durante la subida
- SweetAlert2 para confirmaciones

### `index_liquidaciones.blade.php` (Liquidaciones)
- Aplicar mismo sistema CSS custom properties
- Unificar las 2 tablas visualmente
- Mejorar filtros con el mismo patrón flexbox
- Agregar indicadores visuales de costos vs ingresos

### Template base
- Los cambios de `index_operaciones` se pueden extraer a un CSS compartido
- Crear `resources/css/tarjetas.css` si se repiten en los 3 archivos

---

## Comandos útiles

```bash
# Verificar que las rutas funcionan
php artisan route:list --name=tarjetas

# Probar vista
php artisan serve
```
