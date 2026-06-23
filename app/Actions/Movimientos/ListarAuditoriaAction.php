<?php

namespace App\Actions\Movimientos;

use App\Models\producto;
use Illuminate\Http\JsonResponse;

class ListarAuditoriaAction
{
    public function __invoke(string $familia, string $idprod): JsonResponse
    {
        $datos = producto::listar_auditoria($familia, $idprod);

        return response()->json(['results' => $datos]);
    }
}
