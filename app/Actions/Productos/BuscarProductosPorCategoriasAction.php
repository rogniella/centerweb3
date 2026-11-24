<?php

namespace App\Actions\Productos;

use App\Models\famcategoria;
use App\Models\producto;
use Illuminate\Database\Eloquent\Builder;

/**
 * Busca productos de una familia aplicando los filtros de la cascada.
 *
 * La cantidad y posición de los niveles surge de famcategorias, por lo que
 * sirve para cualquier familia configurada, no solo para cristales.
 */
class BuscarProductosPorCategoriasAction
{
    /** Valor legacy que representa "[Todos]" en los combos. */
    private const CODIGO_TODOS = '00';

    /** Códigos que, para una familia y nivel, se tratan como equivalentes. */
    private const ALIASES = [
        'LEN' => [
            0 => ['S' => ['S', 'L']],
        ],
    ];

    /**
     * @param  array<int, string|null>  $niveles
     * @return array<int, array{idweb: int, descripcion: string, codigo: string, categoria: string, precio: float, precio2: float}>
     */
    public function __invoke(string $familia, array $niveles = [], string $descripcion = ''): array
    {
        $tablaProductos = (new producto)->getTable();

        $categorias = famcategoria::query()
            ->where('FCat_Familia', $familia)
            ->orderBy('FCat_IdCategoria')
            ->get();

        $consulta = producto::query()
            ->select([
                "{$tablaProductos}.Prod_idWEB as idweb",
                "{$tablaProductos}.Prod_Id as codigo",
                "{$tablaProductos}.Prod_Categoria as categoria",
                "{$tablaProductos}.Prod_Descripcion as descripcion",
                "{$tablaProductos}.Prod_Precio as precio",
                "{$tablaProductos}.Prod_Precio2 as precio2",
            ])
            ->whereRaw("TRIM({$tablaProductos}.Prod_Familia) = ?", [$familia])
            ->whereRaw("COALESCE(TRIM({$tablaProductos}.Prod_Estado), '') <> 'I'");

        foreach ($categorias->values() as $indice => $categoria) {
            $codigo = trim((string) ($niveles[$indice] ?? ''));

            if ($codigo === '' || $codigo === self::CODIGO_TODOS) {
                continue;
            }

            // Mismo criterio que el VB: MID(Prod_Categoria, PosIni, CantPos) = código elegido.
            // Si el código tiene alias (LEN/S), se acepta cualquiera de sus valores.
            $consulta->where(function (Builder $consultaNivel) use ($tablaProductos, $categoria, $codigo, $familia, $indice): void {
                foreach ($this->codigosParaNivel($familia, $indice, $codigo) as $valor) {
                    $consultaNivel->orWhereRaw(
                        "LOWER(TRIM(SUBSTR(TRIM({$tablaProductos}.Prod_Categoria), ?, ?))) = ?",
                        [
                            (int) $categoria->FCat_PosIni,
                            mb_strlen(mb_strtolower($valor)),
                            mb_strtolower($valor),
                        ]
                    );
                }
            });
        }

        // Se escapa "!", "%" y "_" para que los comodines escritos por el usuario
        // se busquen literalmente en MySQL y SQLite.
        $descripcion = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], trim($descripcion));

        if ($descripcion !== '') {
            $consulta->whereRaw(
                "TRIM({$tablaProductos}.Prod_Descripcion) LIKE ? ESCAPE '!'",
                ['%'.$descripcion.'%']
            );
        }

        return $consulta
            ->orderBy("{$tablaProductos}.Prod_Precio")
            ->orderBy("{$tablaProductos}.Prod_Descripcion")
            ->get()
            ->map(function ($producto): array {
                return [
                    'idweb' => (int) $producto->idweb,
                    'descripcion' => trim((string) $producto->descripcion),
                    'codigo' => trim((string) $producto->codigo),
                    'categoria' => trim((string) $producto->categoria),
                    'precio' => (float) $producto->precio,
                    'precio2' => (float) $producto->precio2,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function codigosParaNivel(string $familia, int $indice, string $codigo): array
    {
        $alias = self::ALIASES[$familia][$indice][mb_strtoupper(trim($codigo))] ?? null;

        return $alias ?? [$codigo];
    }
}
