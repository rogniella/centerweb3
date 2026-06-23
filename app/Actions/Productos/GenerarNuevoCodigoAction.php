<?php

namespace App\Actions\Productos;

use App\Models\producto;
use Illuminate\Http\JsonResponse;

class GenerarNuevoCodigoAction
{
    public function __invoke(string $familia): JsonResponse
    {
        $NvoCodigo = producto::generarNvoCodigo($familia);

        return response()->json(['NvoCodigo' => $NvoCodigo]);
    }
}
