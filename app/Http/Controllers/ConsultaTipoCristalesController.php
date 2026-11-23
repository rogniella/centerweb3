<?php

namespace App\Http\Controllers;

use App\Actions\Categorias\CargarCategoriasFamiliaAction;
use App\Actions\Cristales\BuscarTiposCristalesAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultaTipoCristalesController extends Controller
{
    private const FAMILIA = 'LEN';

    public function index(CargarCategoriasFamiliaAction $action): View
    {
        return view('cristales.consulta_tipo', [
            'categorias' => $action(self::FAMILIA),
        ]);
    }

    public function categorias(Request $request, CargarCategoriasFamiliaAction $action): JsonResponse
    {
        $validated = $request->validate([
            'nivel' => 'required|integer|min:0',
            'niveles' => 'nullable|array',
            'niveles.*' => 'nullable|string|max:15',
        ]);

        $nivel = $action->opcionesParaNivel(
            self::FAMILIA,
            (int) $validated['nivel'],
            $validated['niveles'] ?? []
        );

        abort_unless($nivel !== null, 404);

        return response()->json($nivel);
    }

    public function buscar(Request $request, BuscarTiposCristalesAction $action): JsonResponse
    {
        $validated = $request->validate([
            'niveles' => 'nullable|array',
            'niveles.*' => 'nullable|string|max:15',
            'descripcion' => 'nullable|string|max:100',
            'esf' => 'nullable|numeric|multiple_of:0.25',
            'cil' => 'nullable|numeric|multiple_of:0.25',
        ], [], [
            'esf' => 'esferico',
            'cil' => 'cilindro',
        ]);

        return response()->json($action(
            self::FAMILIA,
            $validated['niveles'] ?? [],
            trim((string) ($validated['descripcion'] ?? '')),
            $this->graduacion($validated['esf'] ?? null),
            $this->graduacion($validated['cil'] ?? null)
        ));
    }

    private function graduacion(mixed $valor): ?float
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        return (float) $valor;
    }
}
