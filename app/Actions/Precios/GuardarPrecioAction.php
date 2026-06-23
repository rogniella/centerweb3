<?php

namespace App\Actions\Precios;

use App\Models\cotizacion;
use App\Models\precio;
use App\Models\producto;
use Illuminate\Http\JsonResponse;

class GuardarPrecioAction
{
    public function __invoke(array $data): JsonResponse|string
    {
        $row = precio::find_producto($data['idprod'] ?? 0);

        if (! $row) {
            $row = new precio;
            $row->idWEB_Prod = $data['idprod'];
        }

        $row->precio = $data['precio'] ?? 0;
        $row->precio2 = $data['precio2'] ?? 0;
        $row->costo = $data['costo'] ?? 0;
        $row->idlista = $data['idlista'] ?? '';

        if (! $row->save()) {
            return 'Error al actualizar tabla Precios';
        }

        $cotiza = 0;
        $aux = cotizacion::mtoEnPesos($data['idlista'] ?? '', 100, '', $cotiza);

        $producto = producto::find($data['idprod']);

        if (! $producto) {
            return 'Error al leer tabla Productos Id:'.($data['idprod'] ?? '');
        }

        $producto->Prod_Precio = ($data['precio'] ?? 0) * $cotiza;
        $producto->Prod_Precio2 = ($data['precio2'] ?? 0) * $cotiza;
        $producto->Prod_Costo = ($data['costo'] ?? 0) * $cotiza;
        $producto->Prod_UsuUltMan = 'ADM_Precio';
        $producto->actualizar();

        return response()->json(['msg' => 'Se actualizo Precios con Éxito!!']);
    }
}
