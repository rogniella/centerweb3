<?php

namespace App\Actions\Precios;

use App\Models\producto;
use Illuminate\Http\JsonResponse;

class ModificarPrecioAction
{
    public function __invoke(array $data): JsonResponse
    {
        $oproducto = producto::findCodigo($data['familia'], $data['codigo']);

        if (! $oproducto) {
            return response()->json([
                'success' => false,
                'error_msg' => 'No se encontró el producto con código ' . $data['codigo'] . ' de la familia ' . $data['familia'],
            ]);
        }

        return match ($data['action'] ?? '') {
            'lee_precio' => response()->json([
                'success' => true,
                'descripcion' => $oproducto->Prod_Descripcion,
                'precio' => $oproducto->Prod_Precio,
                'precio2' => $oproducto->Prod_Precio2,
            ]),
            'modifica_precio' => $this->actualizarPrecio($oproducto, $data),
            default => response()->json(['success' => false, 'error_msg' => 'Acción no válida']),
        };
    }

    private function actualizarPrecio(producto $oproducto, array $data): JsonResponse
    {
        $oproducto->Prod_Precio = $data['monto'];
        $oproducto->Prod_Precio2 = $data['monto2'];
        $oproducto->Prod_UsuUltMan = 'PrecioWeb';
        $oproducto->actualizar();

        return response()->json(['success' => true]);
    }
}
