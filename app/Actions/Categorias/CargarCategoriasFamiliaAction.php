<?php

namespace App\Actions\Categorias;

use App\Models\famcategoria;
use App\Models\famitemcat;
use App\Models\producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Construye la cascada de combos definida en famcategorias para una familia.
 *
 * Las opciones combinan el catálogo de famitemcat con códigos DISTINCT
 * observados en productos.Prod_Categoria, porque en la BD legacy no todos
 * los códigos están catalogados. El número de niveles depende de la familia.
 */
class CargarCategoriasFamiliaAction
{
    /** Valor legacy que representa "[Todos]" en los combos. */
    private const CODIGO_TODOS = '00';

    /**
     * Devuelve todos los niveles con sus opciones para el render inicial de la vista.
     *
     * @param  array<int, string|null>  $seleccion
     * @return array<int, array{indice: int, id: int, descripcion: string, posicion: int, longitud: int, opciones: array<int, array{codigo: string, descripcion: string}>}>
     */
    public function __invoke(string $familia, array $seleccion = []): array
    {
        return $this->niveles($familia, $this->normalizarSeleccion($seleccion));
    }

    /**
     * Recalcula un solo nivel para la petición AJAX de la cascada.
     *
     * Se reconstruyen todos los niveles de la familia con la selección
     * recibida y luego se devuelve solo el solicitado; así el combo hijo
     * respeta al padre.
     *
     * @param  array<int, string|null>  $seleccion
     * @return array{indice: int, id: int, descripcion: string, posicion: int, longitud: int, opciones: array<int, array{codigo: string, descripcion: string}>}|null
     */
    public function opcionesParaNivel(string $familia, int $nivel, array $seleccion = []): ?array
    {
        $niveles = $this->niveles($familia, $this->normalizarSeleccion($seleccion));

        return $niveles[$nivel] ?? null;
    }

    /**
     * Arma la estructura de niveles de la familia recibida.
     *
     * El "indice" es posicional y no es el FCat_IdCategoria: la vista y el
     * endpoint usan ese índice para identificar cada combo de la cascada.
     *
     * @param  array<int, string>  $seleccion
     * @return array<int, array{indice: int, id: int, descripcion: string, posicion: int, longitud: int, opciones: array<int, array{codigo: string, descripcion: string}>}>
     */
    private function niveles(string $familia, array $seleccion): array
    {
        $categorias = famcategoria::query()
            ->where('FCat_Familia', $familia)
            ->orderBy('FCat_IdCategoria')
            ->get();

        return $categorias->values()
            ->map(function (famcategoria $categoria, int $indice) use ($familia, $categorias, $seleccion): array {
                return [
                    'indice' => $indice,
                    'id' => (int) $categoria->FCat_IdCategoria,
                    'descripcion' => trim((string) $categoria->FCat_Descripcion),
                    'posicion' => (int) $categoria->FCat_PosIni,
                    'longitud' => (int) $categoria->FCat_CantPos,
                    'opciones' => $this->opciones($familia, $categorias, $categoria, $seleccion, $indice),
                ];
            })
            ->all();
    }

    /**
     * Obtiene las opciones de un nivel combinando catálogo y productos.
     *
     * Se indexa por código en minúsculas para deduplicar ambas fuentes y que
     * la comparación no dependa de mayúsculas/minúsculas.
     *
     * @param  Collection<int, famcategoria>  $categorias
     * @param  array<int, string>  $seleccion
     * @return array<int, array{codigo: string, descripcion: string}>
     */
    private function opciones(string $familia, Collection $categorias, famcategoria $categoria, array $seleccion, int $indice): array
    {
        $porCodigo = collect();

        // El VB filtraba los ítems por el código elegido en el nivel anterior.
        // Los datos legacy usan '*' como comodín, así que se aceptan ambos casos.
        $codigoPadre = $seleccion[$indice - 1] ?? null;

        $catalogadas = famitemcat::query()
            ->where('FCatItem_IdCategoria', $categoria->FCat_IdCategoria);

        if ($codigoPadre !== null) {
            $catalogadas->where(function (Builder $consulta) use ($codigoPadre): void {
                $consulta->where('FCatItem_CodPadre', '*')
                    ->orWhere('FCatItem_CodPadre', $codigoPadre);
            });
        }

        $catalogadas
            ->orderBy('FCatItem_Descripcion')
            ->get(['FCatItem_Codigo', 'FCatItem_Descripcion'])
            ->each(function (famitemcat $item) use ($porCodigo): void {
                $codigo = trim((string) $item->FCatItem_Codigo);

                if ($codigo === '' || $codigo === self::CODIGO_TODOS) {
                    return;
                }

                $descripcion = trim((string) $item->FCatItem_Descripcion);
                // Si no hay descripción catalogada, se muestra el propio código.
                $porCodigo->put(mb_strtolower($codigo), [
                    'codigo' => $codigo,
                    'descripcion' => $descripcion !== '' ? $descripcion : $codigo,
                ]);
            });

        // No todos los códigos de Prod_Categoria están en famitemcat; se agregan
        // los valores DISTINCT encontrados para no perder opciones reales.
        $this->codigosDesdeProductos($familia, $categorias, $categoria, $seleccion, $indice)
            ->each(function (string $codigo) use ($porCodigo): void {
                $codigo = trim($codigo);

                if ($codigo === '' || $codigo === self::CODIGO_TODOS) {
                    return;
                }

                $clave = mb_strtolower($codigo);

                if (! $porCodigo->has($clave)) {
                    $porCodigo->put($clave, [
                        'codigo' => $codigo,
                        'descripcion' => $codigo,
                    ]);
                }
            });

        // Se ordena por descripción y se antepone "[Todos]", igual que en
        // el combo original de ChoicesCatego.frm.
        $opciones = $porCodigo
            ->sortBy(fn (array $opcion): string => mb_strtolower($opcion['descripcion']))
            ->values()
            ->all();

        return array_merge([
            ['codigo' => self::CODIGO_TODOS, 'descripcion' => '[Todos]'],
        ], $opciones);
    }

    /**
     * Extrae códigos DISTINCT de Prod_Categoria para el nivel indicado.
     *
     * FCat_PosIni y FCat_CantPos definen el substring dentro de Prod_Categoria
     * (por ejemplo: posición 4 y longitud 3 para "Producto Lente").
     *
     * @param  Collection<int, famcategoria>  $categorias
     * @param  array<int, string>  $seleccion
     * @return Collection<int, string>
     */
    private function codigosDesdeProductos(string $familia, Collection $categorias, famcategoria $categoria, array $seleccion, int $hasta): Collection
    {
        $tabla = (new producto)->getTable();

        // TRIM y COALESCE son necesarios porque la tabla legacy guarda espacios
        // y estados nulos además de 'I' para los productos inactivos.
        $consulta = producto::query()
            ->selectRaw("DISTINCT TRIM(SUBSTR(TRIM({$tabla}.Prod_Categoria), ?, ?)) AS codigo", [
                (int) $categoria->FCat_PosIni,
                (int) $categoria->FCat_CantPos,
            ])
            ->whereRaw("TRIM({$tabla}.Prod_Familia) = ?", [$familia])
            ->whereRaw("COALESCE(TRIM({$tabla}.Prod_Estado), '') <> 'I'");

        foreach ($seleccion as $indice => $codigo) {
            // Solo intervienen los niveles anteriores al que se está resolviendo.
            if ($indice >= $hasta) {
                continue;
            }

            $categoriaPadre = $categorias->get($indice);

            if (! $categoriaPadre instanceof famcategoria) {
                continue;
            }

            // Mismo criterio que el VB: MID(Prod_Categoria, PosIni, CantPos) = código elegido.
            // Se usa la longitud del código elegido, no la longitud declarada de la categoría.
            $consulta->whereRaw(
                "LOWER(TRIM(SUBSTR(TRIM({$tabla}.Prod_Categoria), ?, ?))) = ?",
                [
                    (int) $categoriaPadre->FCat_PosIni,
                    mb_strlen(mb_strtolower($codigo)),
                    mb_strtolower($codigo),
                ]
            );
        }

        return $consulta->pluck('codigo');
    }

    /**
     * Normaliza los parámetros recibidos por AJAX.
     *
     * Descarta claves no numéricas y valores vacíos o "00" ([Todos]), de modo
     * que solo se apliquen como filtros los códigos efectivamente elegidos.
     *
     * @param  array<int, string|null>  $seleccion
     * @return array<int, string>
     */
    private function normalizarSeleccion(array $seleccion): array
    {
        $normalizada = [];

        foreach ($seleccion as $indice => $codigo) {
            if (! is_numeric($indice)) {
                continue;
            }

            $codigo = trim((string) $codigo);

            if ($codigo === '' || $codigo === self::CODIGO_TODOS) {
                continue;
            }

            $normalizada[(int) $indice] = $codigo;
        }

        return $normalizada;
    }
}
