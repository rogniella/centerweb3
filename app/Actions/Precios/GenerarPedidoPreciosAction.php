<?php

namespace App\Actions\Precios;

use App\Models\producto;
use Illuminate\Http\JsonResponse;

class GenerarPedidoPreciosAction
{
    public function __invoke(array $data): JsonResponse
    {
        $casos = 0;
        $datos = producto::listar(
            $data['filtro_flia'] ?? '',
            $data['filtro1'] ?? '',
            10000,
            $data['filtro2'] ?? '',
            $data['filtro3'] ?? '',
            $data['filtroEstado'] ?? '',
            $data['filtroMarca'] ?? ''
        );

        foreach ($datos as $elem) {
            if ($elem->prod_id === '9999' || $elem->prod_id === '0') {
                continue;
            }

            $oproducto = producto::findCodigo($elem->prod_familia, $elem->prod_id);
            $oproducto->Prod_Precio = redondear_a_10($oproducto->Prod_Precio * 1.10);
            $oproducto->Prod_Precio2 = redondear_a_10($oproducto->Prod_Precio2 * 1.10);
            $oproducto->Prod_UsuUltMan = 'LWebPrecio';
            $oproducto->actualizar();
            $casos++;
        }

        return response()->json(['results' => 'Aumento 10%  Casos:' . $casos]);
    }
}
