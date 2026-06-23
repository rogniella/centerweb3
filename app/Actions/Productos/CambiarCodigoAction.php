<?php

namespace App\Actions\Productos;

use App\Models\producto;
use Illuminate\Http\JsonResponse;

class CambiarCodigoAction
{
    public function __invoke(array $data): JsonResponse
    {
        $cantidad = 0;
        $datos = producto::listar(
            $data['filtro_flia'] ?? '',
            $data['filtro1'] ?? '',
            10000,
            $data['filtro2'] ?? '',
            $data['filtro3'] ?? '',
            $data['filtroEstado'] ?? ''
        );

        foreach ($datos as $elem) {
            if ($elem->prod_id === '9999' || $elem->prod_id === '0') {
                continue;
            }

            if (substr($elem->prod_id, 0, 1) === 'A') {
                $res = producto::cambia_codigo(
                    $data['filtro_flia'],
                    $elem->prod_id,
                    substr($elem->prod_id, 1, 4)
                );
                $cantidad++;
            }
        }

        return response()->json(['results' => 'Ok Productos Convertidos:' . $cantidad]);
    }
}
