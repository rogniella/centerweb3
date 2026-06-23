<?php

namespace App\Actions\Publicaciones;

use App\Models\producto;
use App\Models\publicacion;
use Illuminate\Http\JsonResponse;

class CrearPublicacionAction
{
    public function __invoke(array $data): JsonResponse|string
    {

        if (! $producto = producto::find($data['idwebProd'])) {
            return ' Error al buscar en tabla Productos Id:'.$data['idwebProd'];
        }

        $row = new publicacion;
        $row->idWEB_Prod = $data['idwebProd'];
        $row->cantidad = $data['cantidad'] ?? 0;
        $row->precio_venta = ($data['precio'] ?? '') === '' ? 0 : $data['precio'];
        $row->observ = $data['observ'] ?? '';
        $row->estado = 'A';
        if (! $row->save()) {
            return ' Error al actualizar en tabla Publicaciones ';
        }

        if (auth()->user()) {
            $producto->Prod_UsuUltMan = auth()->user()->name;
        }
        $producto->insertHistoria('Publicación OnLine', '', '');

        return response()->json(['msg' => 'Ok']);
    }
}
