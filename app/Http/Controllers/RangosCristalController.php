<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarRangoCristalRequest;
use App\Models\cristal;
use App\Models\producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class RangosCristalController extends Controller
{
    private const FAMILIA = 'LEN';

    private const CAMPOS = [
        'Cri_esf',
        'Cri_esf_neg',
        'Cri_cil',
        'Cri_cil_neg',
        'Cri_add',
        'Cri_rango',
        'Cri_metodo',
    ];

    public function index(string $producto): JsonResponse
    {
        $this->producto($producto);

        return response()->json(
            $this->rangos($producto)
                ->map(fn (cristal $rango): array => $this->serializar($rango))
                ->values()
                ->all()
        );
    }

    public function store(string $producto, GuardarRangoCristalRequest $request): JsonResponse
    {
        $this->producto($producto);

        $rango = new cristal;
        $rango->Cri_familia = self::FAMILIA;
        $rango->Cri_id = trim($producto);
        $rango->fill($request->safe()->only(self::CAMPOS));
        $rango->save();

        return response()->json($this->serializar($rango), 201);
    }

    public function update(string $producto, int $rango, GuardarRangoCristalRequest $request): JsonResponse
    {
        $this->producto($producto);
        $registro = $this->encontrarRango($producto, $rango);
        $registro->fill($request->safe()->only(self::CAMPOS));
        $registro->save();

        return response()->json($this->serializar($registro));
    }

    public function destroy(string $producto, int $rango): JsonResponse
    {
        $this->producto($producto);
        $registro = $this->encontrarRango($producto, $rango);

        return response()->json(['ok' => (bool) $registro->delete()]);
    }

    private function producto(string $codigo): producto
    {
        $tablaProductos = (new producto)->getTable();

        return producto::query()
            ->whereRaw("TRIM({$tablaProductos}.Prod_Familia) = ?", [self::FAMILIA])
            ->whereRaw("TRIM({$tablaProductos}.Prod_Id) = ?", [trim($codigo)])
            ->firstOrFail();
    }

    /**
     * @return Collection<int, cristal>
     */
    private function rangos(string $producto): Collection
    {
        $tablaCristales = (new cristal)->getTable();

        return cristal::query()
            ->whereRaw("TRIM({$tablaCristales}.Cri_familia) = ?", [self::FAMILIA])
            ->whereRaw("TRIM({$tablaCristales}.Cri_id) = ?", [trim($producto)])
            ->orderBy("{$tablaCristales}.Cri_rango_id")
            ->get(array_merge(['Cri_rango_id', 'Cri_familia', 'Cri_id'], self::CAMPOS));
    }

    private function encontrarRango(string $producto, int $id): cristal
    {
        $tablaCristales = (new cristal)->getTable();

        return cristal::query()
            ->where("{$tablaCristales}.Cri_rango_id", $id)
            ->whereRaw("TRIM({$tablaCristales}.Cri_familia) = ?", [self::FAMILIA])
            ->whereRaw("TRIM({$tablaCristales}.Cri_id) = ?", [trim($producto)])
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializar(cristal $rango): array
    {
        return [
            'Cri_rango_id' => (int) $rango->Cri_rango_id,
            'Cri_familia' => trim((string) $rango->Cri_familia),
            'Cri_id' => trim((string) $rango->Cri_id),
            'Cri_esf' => $rango->Cri_esf,
            'Cri_esf_neg' => $rango->Cri_esf_neg,
            'Cri_cil' => $rango->Cri_cil,
            'Cri_cil_neg' => $rango->Cri_cil_neg,
            'Cri_add' => $rango->Cri_add,
            'Cri_rango' => $rango->Cri_rango,
            'Cri_metodo' => $rango->Cri_metodo,
        ];
    }
}
