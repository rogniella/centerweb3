<?php

namespace App\Actions\Publicaciones;

use App\Models\publicacion;
use App\Models\tienda_producto;
use Illuminate\Http\JsonResponse;

class ListarPublicacionesAction
{
    public function __invoke(string $filtroEstado = '', string $filtroDescripcion = ''): JsonResponse
    {
        $datos = publicacion::listar($filtroEstado, $filtroDescripcion);
        $array_prod = tienda_producto::listar($filtroDescripcion);
        $sku = array_column($array_prod, 'sku');

        foreach ($datos as $row) {
            $key = array_search($row->prod_id, $sku);
            if (is_numeric($key)) {
                $row->tienda_name = $array_prod[$key]->name;
                $row->tienda_precio = $array_prod[$key]->regular_price;
                if ($array_prod[$key]->stock != '') {
                    $row->observ = $row->observ.' En Tienda:'.$array_prod[$key]->stock;
                }
            }
        }

        return response()->json(['results' => $datos, 'tienda' => $array_prod]);
    }
}
