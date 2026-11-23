<?php

namespace App\Actions\Cristales;

use App\Actions\Productos\BuscarProductosPorCategoriasAction;
use App\Models\cristal;
use Illuminate\Support\Collection;

/**
 * Busca productos con la cascada genérica y les agrega los rangos de cristales.
 *
 * La búsqueda y los filtros viven en BuscarProductosPorCategoriasAction; esta
 * clase solo enriquece el resultado con la tabla cristales, que existe para
 * la familia LEN. Otras opciones pueden usar la Action genérica directamente.
 */
class BuscarTiposCristalesAction
{
    public function __construct(
        private BuscarProductosPorCategoriasAction $buscarProductos,
        private FiltrarGraduacionesCristalAction $filtrarGraduaciones,
    ) {}

    /**
     * @param  array<int, string|null>  $niveles
     * @return array<int, array{idweb: int, descripcion: string, codigo: string, categoria: string, precio: float, precio2: float, rangos: array<int, string>}>
     */
    public function __invoke(
        string $familia,
        array $niveles = [],
        string $descripcion = '',
        ?float $esf = null,
        ?float $cil = null
    ): array {
        $productos = ($this->buscarProductos)($familia, $niveles, $descripcion);

        if ($productos === []) {
            return [];
        }

        $rangosPorProducto = $this->rangosPorProducto(
            $familia,
            collect($productos)->pluck('codigo')->map(fn ($codigo): string => trim((string) $codigo))->all()
        );

        return collect($productos)
            ->filter(function (array $producto) use ($familia, $rangosPorProducto, $esf, $cil): bool {
                $datos = $rangosPorProducto[$this->claveProducto($familia, (string) $producto['codigo'])] ?? null;

                if ($datos === null) {
                    return $esf === null && $cil === null;
                }

                return ($this->filtrarGraduaciones)($datos['cristales'], $esf, $cil);
            })
            ->map(function (array $producto) use ($familia, $rangosPorProducto): array {
                $datos = $rangosPorProducto[$this->claveProducto($familia, (string) $producto['codigo'])] ?? null;
                $producto['rangos'] = $datos['rangos'] ?? [];

                return $producto;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $codigos
     * @return Collection<string, array{cristales: Collection, rangos: array<int, string>}>
     */
    private function rangosPorProducto(string $familia, array $codigos): Collection
    {
        $tablaCristales = (new cristal)->getTable();
        $marcadores = implode(', ', array_fill(0, count($codigos), '?'));

        return cristal::query()
            ->whereRaw("TRIM({$tablaCristales}.Cri_familia) = ?", [$familia])
            ->whereRaw("TRIM({$tablaCristales}.Cri_id) IN ({$marcadores})", $codigos)
            ->get([
                "{$tablaCristales}.Cri_familia",
                "{$tablaCristales}.Cri_id",
                "{$tablaCristales}.Cri_esf",
                "{$tablaCristales}.Cri_esf_neg",
                "{$tablaCristales}.Cri_cil",
                "{$tablaCristales}.Cri_cil_neg",
            ])
            ->groupBy(fn ($cristal): string => $this->claveProducto(
                (string) $cristal->Cri_familia,
                (string) $cristal->Cri_id
            ))
            ->map(function (Collection $registros): array {
                $registrosOrdenados = $registros
                    ->sort(function ($a, $b): int {
                        return [(float) $a->Cri_esf, (float) $a->Cri_cil]
                            <=> [(float) $b->Cri_esf, (float) $b->Cri_cil];
                    })
                    ->values();

                return [
                    'cristales' => $registrosOrdenados,
                    'rangos' => $registrosOrdenados
                        ->map(fn ($cristal): string => sprintf(
                            'Esf %s/%s Cil %s/%s',
                            $this->formatearNumero($cristal->Cri_esf),
                            $this->formatearNumero($cristal->Cri_esf_neg),
                            $this->formatearNumero($cristal->Cri_cil),
                            $this->formatearNumero($cristal->Cri_cil_neg)
                        ))
                        ->values()
                        ->all(),
                ];
            });
    }

    private function claveProducto(string $familia, string $codigo): string
    {
        return trim($familia).'|'.trim($codigo);
    }

    private function formatearNumero(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        return rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.');
    }
}
