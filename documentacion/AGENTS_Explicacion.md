# Explicación del archivo AGENTS.md

Este archivo contiene las **directrices que sigue el asistente de IA (opencode)** al trabajar en esta aplicación Laravel. Está estructurado en varias secciones. A continuación, se explica cada una en castellano.

---

## 1. Reglas fundamentales (`foundation rules`)

### Contexto base
La aplicación usa **Laravel 13** con PHP 8.4 y varios paquetes del ecosistema (Sanctum, Boost, MCP, PHPUnit 11). El asistente debe conocer y respetar estas versiones.

### Activación de Skills (habilidades)
El proyecto tiene skills específicas en `**/skills/**`. El asistente debe activar la skill adecuada según el dominio en el que esté trabajando.

### Convenciones de código
- Seguir las convenciones existentes del proyecto.
- Usar nombres descriptivos para variables y métodos (ej. `isRegisteredForDiscounts` en vez de `discount`).
- Revisar componentes existentes antes de crear uno nuevo.

### Scripts de verificación
No crear scripts de prueba sueltos si ya existen tests unitarios o de funcionalidad que cubran esa característica.

### Estructura y arquitectura
- Mantener la estructura de directorios actual.
- No cambiar dependencias sin aprobación.

### Frontend
Si un cambio frontend no se refleja en la UI, puede ser necesario ejecutar `npm run build`, `npm run dev` o `composer run dev`.

### Documentación
Solo crear archivos de documentación si el usuario lo pide explícitamente.

### Respuestas
Ser conciso y centrarse en lo importante, sin explicar lo obvio.

---

## 2. Reglas de Laravel Boost (`boost rules`)

### Herramientas
Laravel Boost es un servidor MCP con herramientas específicas:
- `database-query` — consultas de solo lectura a la BD.
- `database-schema` — inspeccionar estructura de tablas.
- `get-absolute-url` — obtener la URL completa del proyecto.
- `browser-logs` — leer logs del navegador.

### Búsqueda en documentación (IMPORTANTE)
- Usar `search-docs` antes de cualquier cambio de código.
- Pasar un array `packages` para acotar resultados.
- Usar consultas amplias y basadas en temas.
- No incluir nombres de paquetes en las consultas.

### Sintaxis de búsqueda
1. Palabras sueltas: funcionan como AND automático.
2. Frases entre comillas: búsqueda exacta por posición.
3. Combinación: palabras + frases entrecomilladas.
4. Múltiples consultas: se comportan como OR.

### Artisan
- Ejecutar comandos Artisan directamente por terminal.
- Usar `php artisan route:list` para inspeccionar rutas, con filtros como `--method`, `--name`, `--path`.
- Leer configuración con `php artisan config:show` o directamente desde `config/`.
- Leer variables de entorno desde `.env`.

### Tinker
- Ejecutar PHP en contexto de la app para depurar.
- No crear modelos sin aprobación del usuario; preferir tests con factories.
- Usar comillas simples para evitar expansión del shell.

---

## 3. Reglas de PHP (`php rules`)

- Usar llaves `{}` en estructuras de control aunque tengan una sola línea.
- Usar _constructor property promotion_ de PHP 8.
- Declarar tipos de retorno explícitos y type hints en todos los parámetros.
- Usar `TitleCase` para las claves de Enum.
- Preferir bloques PHPDoc sobre comentarios en línea.
- Usar definiciones de tipo `array shape` en PHPDoc.

---

## 4. Despliegue (`deployments rules`)

Laravel puede desplegarse con **Laravel Cloud**, la forma más rápida de desplegar y escalar aplicaciones Laravel en producción.

---

## 5. Reglas principales de Laravel (`laravel/core rules`)

### Hacer las cosas "a la Laravel"
- Usar `php artisan make:` para crear archivos (migraciones, controladores, modelos, etc.).
- Para clases PHP genéricas, usar `php artisan make:class`.
- Pasar `--no-interaction` a todos los comandos Artisan.

### Creación de modelos
Al crear modelos, también crear factories y seeders útiles.

### APIs y Eloquent Resources
Por defecto, usar Eloquent API Resources y versionado de API, a menos que las rutas existentes sigan otra convención.

### Generación de URLs
Usar rutas nombradas y la función `route()`.

### Tests
- Usar factories para crear modelos en tests.
- Seguir la convención existente para Faker (`$this->faker` o `fake()`).
- Usar `php artisan make:test` para crear tests (con `--unit` para tests unitarios).

### Error de Vite
Si aparece `ViteException: Unable to locate file in Vite manifest`, ejecutar `npm run build`.

---

## 6. Reglas de PHPUnit (`phpunit/core rules`)

- Todos los tests deben escribirse como clases PHPUnit (no Pest).
- Cada vez que se actualice un test, ejecutar ese test individual.
- Cuando los tests relacionados pasen, preguntar al usuario si quiere ejecutar toda la suite.
- Los tests deben cubrir caminos felices, de fallo y casos límite.
- No eliminar tests ni archivos de test sin aprobación.

### Ejecución de tests
- Test único filtrado: `php artisan test --compact --filter=testName`
- Archivo completo: `php artisan test --compact tests/Feature/ExampleTest.php`
- Todos: `php artisan test --compact`
