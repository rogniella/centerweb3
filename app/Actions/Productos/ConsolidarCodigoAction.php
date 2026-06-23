<?php

namespace App\Actions\Productos;

use App\Models\producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ConsolidarCodigoAction
{
    public function __invoke(array $data): JsonResponse
    {
        $cantidad = 0;
        $codMinimo = 99999;

        foreach ($data['selectedRows'] as $row) {
            if ($row < $codMinimo) {
                $codMinimo = $row;
            }

            $res = producto::reemplaza_codigo($data['familia'], $row, $data['cod_destino']);
            if ($res !== '') {
                throw new \Exception('Error al reemplazar código: ' . $res);
            }
            $cantidad++;
        }

        $datos = DB::select('SELECT Flia_MaxId FROM familias WHERE Flia_Id = ?', [$data['familia']]);
        $naux = $datos[0]->Flia_MaxId;

        if ($codMinimo < $naux) {
            DB::update('UPDATE familias SET Flia_MaxId = ? WHERE Flia_Id = ?', [$codMinimo - 1, $data['familia']]);
            displaylog('Actualizo el Codigo Minimo de la Familia');
        }

        return response()->json(['results' => 'Ok Productos:' . $cantidad]);
    }
}
