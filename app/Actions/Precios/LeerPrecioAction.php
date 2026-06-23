<?php

namespace App\Actions\Precios;

use App\Models\precio;
use Illuminate\Http\JsonResponse;

class LeerPrecioAction
{
    public function __invoke(int $idprod): JsonResponse
    {
        $datos = precio::find_producto($idprod);

        if (! $datos) {
            return response()->json([
                'idLista' => '',
                'costo' => 0,
                'precio' => 0,
                'precio2' => 0,
            ]);
        }

        return response()->json($datos);
    }
}
