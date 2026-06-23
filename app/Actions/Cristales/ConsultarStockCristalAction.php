<?php

namespace App\Actions\Cristales;

use App\Models\inventario;
use App\Models\producto;
use Illuminate\Http\JsonResponse;

class ConsultarStockCristalAction
{
    public function __invoke(string $codigo): JsonResponse
    {
        $oproducto = producto::findCodigo('CRI', $codigo);

        if (! $oproducto) {
            return response()->json(['stock01' => 'ERR1', 'stock02' => 'ERR1']);
        }

        $stock01 = 0;
        $stock02 = 0;

        if ($inv = inventario::findCodigo($oproducto->Prod_idWEB, 1)) {
            $stock01 = $inv->Inv_Stock;
        }
        if ($inv = inventario::findCodigo($oproducto->Prod_idWEB, 2)) {
            $stock02 = $inv->Inv_Stock;
        }

        $stock01 = str_pad($stock01, 4, ' ', STR_PAD_LEFT);
        $stock02 = str_pad($stock02, 4, ' ', STR_PAD_LEFT);

        return response()->json(['stock01' => $stock01, 'stock02' => $stock02]);
    }
}
