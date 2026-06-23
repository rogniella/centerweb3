<?php

namespace App\Actions\Publicaciones;

use App\Models\producto;
use App\Models\publicacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegistrarVentaOnlineAction
{
    public function __invoke(array $data): JsonResponse|string
    {
        if (! $row = publicacion::find($data['idweb'])) {
            return ' Error al buscar en tabla Publicaciones Id:'.$data['idweb'];
        }

        return match ($data['accion']) {
            'P' => $this->pausar($row, $data),
            'V' => $this->vender($row, $data),
            default => response()->json(['msg' => 'Acción no válida']),
        };
    }

    private function pausar(publicacion $row, array $data): JsonResponse
    {
        $row->observ = $data['observ'] ?? '';
        $row->estado = 'P';

        if (! $row->save()) {
            return response()->json(['msg' => 'Error al actualizar en tabla Publicaciones'], 500);
        }

        return response()->json(['msg' => 'Publicacion Pausada con Éxito!!']);
    }

    private function vender(publicacion $row, array $data): JsonResponse
    {
        return DB::transaction(function () use ($row, $data) {
            $row->precio_venta = ($data['precio'] ?? '') === '' ? 0 : $data['precio'];
            $row->observ = $data['observ'] ?? '';
            $row->estado = 'V';

            if (! $row->save()) {
                throw new \Exception('Error al actualizar en tabla Publicaciones');
            }

            if (! $producto = producto::find($row->idWEB_prod)) {
                throw new \Exception('Error al buscar en tabla Productos Id:'.$row->idWEB_prod);
            }

            $producto->Prod_UsuUltMan = 'Vta.Online';
            $producto->addMovimiento('I', 1, $data['precio'] ?? 0,
                $data['idweb'], 99, 'Pasa para Vta Online desde Suc:'.($data['sucursal'] ?? ''), '', $data['sucursal'] ?? 0);
            $producto->addMovimiento('V_OnLine', 1, $data['precio'] ?? 0,
                $data['idweb'], 0, 'Venta con Producto de la Suc:'.($data['sucursal'] ?? ''), '', 99);

            return response()->json(['msg' => 'Venta OnLine Registrada con Éxito!!']);
        });
    }
}
