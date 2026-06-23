<?php

namespace App\Actions\Movimientos;

use App\Models\producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuscarMovimientosAction
{
    public function __invoke(Request $request): JsonResponse
    {
        $datos = producto::buscar_movimientos(
            $request->filtro_fecini,
            $request->filtro_fecfin,
            $request->filtro_sucursal ?? '0',
            $request->filtro_tipo_operacion ?? '',
            $request->filtro_familia ?? '',
            $request->filtro_idprod ?? '',
            $request->filtro_descripcion ?? '',
            $request->filtro_cero ?? 'N',
            1000
        );

        return response()->json(['results' => $datos]);
    }
}
