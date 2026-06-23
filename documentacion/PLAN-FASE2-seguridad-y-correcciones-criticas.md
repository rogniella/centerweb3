# Plan Fase 2 — Seguridad y Correcciones Críticas

> Generado el: 2026-06-14
> Basado en análisis post-Fase 1 (tests, pint, phpstan)

---

## Resumen

Tras completar la Fase 1 (infraestructura de tests, code style y correcciones en
`cliente`, `producto`, `proveedor`), quedan **22 problemas críticos** que deben
resolverse. Este documento detalla cada problema, su prioridad y el enfoque
de corrección.

---

## Prioridades

| Prioridad | Área | Cantidad |
|-----------|------|----------|
| **P0** | SQL Injection — Controllers | ~12 |
| **P0** | SQL Injection — Models | ~5 |
| **P0** | Funciones indefinidas | 2 |
| **P1** | Namespaces incorrectos | 2 |
| **P1** | Clase faltante | 1 |
| **P2** | Lógica de `save()` bug | 2 |

---

## P0 — SQL Injection en Controllers

### Problema

Los controllers reciben input directamente de `$_GET`, `$_POST` o Request y lo
interpolan en cadenas SQL sin usar parámetros vinculados (`?` placeholders).

### Archivos y ubicaciones

#### `app/Http/Controllers/ClientesController.php`

| Línea | Código actual | Fix |
|-------|---------------|-----|
| 71 | `"WHERE cli_Id= $elem->cli_id"` | `"WHERE cli_Id = ?"` con `[$elem->cli_id]` |
| 79 | `"cli_documento = '$elem->cli_cuil'"` | `"cli_documento = ?"` con `[$elem->cli_cuil]` |
| 85 | `"WHERE cli_Id= $elem->cli_id"` | igual que línea 71 |
| 318 | `"where MDes_IdProv = ".$cta` | `"WHERE MDes_IdProv = ?"` con `[$cta]` |
| 351 | `"MPag_IdCompra=".$elem['MCaj_Id']` | `"MPag_IdCompra = ?"` con `[$elem['MCaj_Id']]` |

#### `app/Http/Controllers/CajasController.php`

| Línea | Código actual | Fix |
|-------|---------------|-----|
| 107-108 | `$_GET['cuenta']` directo | parámetro vinculado |
| 211-224 | `$sucursal`, `$fecha`, `$fechafin` concatenados | todos a `?` |

#### `app/Http/Controllers/CierresController.php`

| Línea | Código actual | Fix |
|-------|---------------|-----|
| 472-477 | `$sucursal`, `$fecha`, `$fechafin` concatenados | todos a `?` |

#### `app/Http/Controllers/EstadisticasController.php`

| Líneas | Código actual | Fix |
|--------|---------------|-----|
| 142, 144, 146, 176, 178, 194, 196 | fechas y sucursales concatenadas | todas a `?` |

### Enfoque de corrección

Para cada query:

1. Reemplazar concatenación de variables con `?` placeholders.
2. Pasar los valores como segundo argumento del array a `DB::select()`,
   `DB::update()`, etc.
3. Verificar que la consulta modificada mantiene la misma lógica de negocio
   (especialmente fechas con operadores `>=`, `<=`).
4. NO cambiar la estructura de la consulta ni los JOINs — solo la vinculación
   de parámetros.

> **Ejemplo genérico:**
> ```php
> // ANTES (vulnerable)
> $consulta = "SELECT * FROM tabla WHERE campo = '$variable'";
> $datos = DB::select($consulta);
>
> // DESPUÉS (seguro)
> $consulta = "SELECT * FROM tabla WHERE campo = ?";
> $datos = DB::select($consulta, [$variable]);
> ```

---

## P0 — SQL Injection en Models

### Archivos y ubicaciones

#### `app/Models/factura.php`

| Línea | Código actual |
|-------|---------------|
| 51 | `">= '".$filtro_fecha."'"` |

La variable `$filtro_fecha` llega desde el controller que la obtiene de
`$_GET['fecha']`.

#### `app/Models/ot.php`

| Línea | Código actual |
|-------|---------------|
| 48 | fechas concatenadas con `.` |
| 190-192 | `"codigo = '$codigo'"` directo |

#### `app/Models/cotizacion.php`

| Línea | Código actual |
|-------|---------------|
| 34-35 | `$monedaOri` y `$fechaCot` concatenados |

#### `app/Models/lote.php`

| Línea | Código actual |
|-------|---------------|
| 28-38 | `$sucursal`, `$lot_estado` concatenados |
| 54 | `$lot_estado` concatenado |
| 76 | `$lot_estado` concatenado |

#### `app/Models/tar_operacion.php`

| Línea | Código actual |
|-------|---------------|
| 62-68 | fechas concatenadas |

#### `app/Models/tar_liquidacion.php`

| Línea | Código actual |
|-------|---------------|
| 46-52 | fechas concatenadas |

### Enfoque de corrección

Ídem Controllers. Nota: algunos de estos models construyen filtros condicionales
con strings. Se debe reestructurar para mantener un array de `$valores` y
agregar `?` en lugar de valores directos.

---

## P0 — Funciones Indefinidas

### `formatoAccess()`

- **Referencias activas:**
  - `app/Models/ComprasProv.php:49` — en `buscar()`
  - `app/Models/ComprasProv.php:78` — en `save()` override

### `convert_from_latin1_to_utf8_recursively()`

- **Referencias activas:**
  - `app/Models/ComprasProv.php:65` — en `listar()`
  - `app/Models/ot_len.php:27-28` — en `find_access()`
  - `app/Http/Controllers/AfipController.php:263` — en catch block

### Enfoque de corrección

1. Buscar en la base de código si las funciones existen en otro lugar (ej.
   legacy `db.php`, `funciones.php`, `includes/`, etc.). Es posible que el
   proyecto antiguo original tuviera estas funciones definidas en archivos que
   ya no se cargan.
2. Si no se encuentran: definir las funciones en `app/helpers.php`.
   - `formatoAccess($texto)`: debe escapar caracteres especiales para Access
     (reemplazar `'` por `''`, manejar codificación).
   - `convert_from_latin1_to_utf8_recursively($data)`: debe convertir strings
     de latin1 a UTF-8 recursivamente en arrays/objetos.
3. Alternativa si las funciones ya no son necesarias (ej. migración completa
   a MySQL): eliminar los calls y la lógica legacy.

---

## P1 — Namespaces Incorrectos

### `app/Models/User.php:48`

```php
// ACTUAL (rompe en runtime)
return $this->belongsTo('App\Perfil', 'perfil_id');

// CORREGIDO
return $this->belongsTo('App\Models\Perfil', 'perfil_id');
```

### `app/Models/Perfil.php:19`

```php
// ACTUAL (rompe en runtime)
return $this->hasMany('App\User');

// CORREGIDO
return $this->hasMany('App\Models\User');
```

### Enfoque de corrección

Simple reemplazo de strings. Estas relaciones se rompen en producción cuando
se accede a `$user->perfil` o `$perfil->users`.

---

## P1 — Clase Faltante

### `app/Http/Controllers/AfipController.php:9`

```php
use App\Models\movimiento_tarjeta;
```

### Enfoque de corrección

1. Verificar si el modelo debería existir pero fue eliminado accidentalmente.
2. Si es necesario: crear `app/Models/movimiento_tarjeta.php` con los campos
   mínimos.
3. Si el import no se usa (import fantasma): eliminar la línea `use`.

---

## P2 — Lógica Incorrecta en `save()` Overrides

### `app/Models/producto.php:77-78`

```php
if (count($campos_modificados) == 0) {
    return false;  // BUG: debería llamar a parent::save()
}
```

**Problema:** Cuando un producto existe y se llama a `save()` sin modificar
ningún campo, retorna `false`. Los callers que verifican el valor de retorno
(ej. `ClientesController:210`: `if (! $registro->save())`) interpretan esto
como error.

**Fix:**
```php
if (count($campos_modificados) == 0) {
    return parent::save($options); // sigue siendo un no-op pero retorna true
}
```

### `app/Models/proveedor.php:88`

```php
if (isset($this->Prov_id)) {
    // Update
    $this->Prov_FecUltMan = fechahorahoy();
} else {
    // Insert
    $this->Prov_FecAlta = fechahorahoy();
}
```

**Problema:** `Prov_id` está en `$fillable`, por lo que una nueva instancia
puede tener `Prov_id` seteado ANTES de `save()`. `isset()` no distingue entre
un nuevo registro con ID asignado y uno existente. El método correcto de
Eloquent es `$this->exists`.

**Fix:**
```php
if ($this->exists) {
    $this->Prov_FecUltMan = fechahorahoy();
} else {
    $this->Prov_FecAlta = fechahorahoy();
}
```

---

## Orden de Ejección Propuesto

```
Fase 2.1 — SQL Injection en Controllers y Models
  ├── Controllers (más expuestos al usuario)
  │   ├── ClientesController.php
  │   ├── CajasController.php
  │   ├── CierresController.php
  │   └── EstadisticasController.php
  └── Models
      ├── factura.php
      ├── ot.php
      ├── cotizacion.php
      ├── lote.php
      ├── tar_operacion.php
      └── tar_liquidacion.php

Fase 2.2 — Funciones indefinidas
  ├── Buscar definiciones legacy
  └── Agregar a helpers.php o eliminar calls

Fase 2.3 — Namespaces y clase faltante
  ├── User.php (App\Perfil → App\Models\Perfil)
  ├── Perfil.php (App\User → App\Models\User)
  └── AfipController.php (movimiento_tarjeta)

Fase 2.4 — Lógica de save()
  ├── producto.php (return false → parent::save)
  └── proveedor.php (isset → $this->exists)
```

---

## Criterio de Aceptación

- [ ] Todos los SQL injections corregidos (ninguna variable de usuario aparece
      dentro de una cadena SQL sin `?` binding).
- [ ] `formatoAccess()` y `convert_from_latin1_to_utf8_recursively()` definidas
      o sus calls eliminados.
- [ ] `User::perfil()` y `Perfil::users()` funcionan sin error de clase no
      encontrada.
- [ ] `AfipController` no lanza ClassNotFoundException.
- [ ] `producto::save()` sin cambios retorna `true`.
- [ ] `proveedor::save()` distingue correctamente insert vs update.
- [ ] Tests existentes (22) siguen pasando tras las correcciones.
