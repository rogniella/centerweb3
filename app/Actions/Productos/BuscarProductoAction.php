<?php

namespace App\Actions\Productos;

use App\Models\producto;
use Illuminate\Http\JsonResponse;

class BuscarProductoAction
{
    public function __invoke(string $familia, string $terms, string $estado = ''): JsonResponse
    {
        $resbd = producto::listar($familia, $terms, 20, '', '', $estado);

        $res = [];
        foreach ($resbd as $elem) {
            $res[] = [
                'id' => $elem->prod_id,
                'idweb' => $elem->prod_idweb,
                'name' => $elem->prod_id . ' - ' . $elem->prod_descripcion,
                'descripcion' => $elem->prod_descripcion,
                'categoria' => $elem->prod_categoria,
                'costo' => $elem->prod_costo,
                'precio' => $elem->prod_precio,
                'stock01' => $elem->stock01,
                'stock02' => $elem->stock02,
                'precio2' => $elem->prod_precio2,
            ];
        }

        return response()->json($res);
    }
}
