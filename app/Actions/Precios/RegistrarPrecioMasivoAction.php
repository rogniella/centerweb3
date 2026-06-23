<?php

namespace App\Actions\Precios;

use App\Models\producto;
use Illuminate\Http\JsonResponse;

class RegistrarPrecioMasivoAction
{
    public function __invoke(array $data): JsonResponse
    {
        $datos = producto::listar(
            $data['filtro_flia'] ?? '',
            $data['filtro1'] ?? '',
            10000,
            $data['filtro2'] ?? '',
            $data['filtro3'] ?? '',
            $data['filtroEstado'] ?? '',
            $data['filtroMarca'] ?? ''
        );

        $cantidad = 0;
        foreach ($datos as $elem) {
            if ($elem->prod_id === '9999' || $elem->prod_id === '0') {
                continue;
            }

            $oproducto = producto::findCodigo($elem->prod_familia, $elem->prod_id);

            if (in_array($data['precio_aplica'] ?? 0, [1, 3])) {
                $oproducto->Prod_Precio = $this->calculoAumento(
                    $oproducto->Prod_Precio,
                    $data['precio_recargo'] ?? 0,
                    $data['precio_tipo'] ?? 1,
                    $data['precio_redondeo'] ?? -1
                );
                $oproducto->Prod_Precio2 = round($oproducto->Prod_Precio * 0.9, 2);
            }

            if (in_array($data['precio_aplica'] ?? 0, [2, 3])) {
                $oproducto->Prod_Costo = $this->calculoAumento(
                    $oproducto->Prod_Costo,
                    $data['precio_recargo'] ?? 0,
                    $data['precio_tipo'] ?? 1,
                    $data['precio_redondeo'] ?? -1
                );
            }

            $oproducto->Prod_UsuUltMan = 'LWebPrecio';
            $oproducto->actualizar();
            $cantidad++;
        }

        return response()->json(['cantidad' => $cantidad]);
    }

    private function calculoAumento(float $valor, float $recargo, string $tipo, string $redondeo): float
    {
        return match ($tipo) {
            '1' => redondear_a_10($valor * (1 + $recargo / 100), $redondeo),
            '2' => redondear_a_10($valor + $recargo, $redondeo),
            '3' => $recargo,
            '4' => max($recargo, $valor),
            default => $valor,
        };
    }
}
