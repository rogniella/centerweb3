<?php

namespace App\Actions\Precios;

use App\Models\producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActualizarPrecioExcelAction
{
    public function __invoke(Request $request): View
    {
        DB::beginTransaction();

        $filename = $request->file('nombre_archivo');

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($filename);

        $detalle = [];
        $detalle[] = 'Usar https://cloudconvert.com/xlsx-to-jpg   para convertir a Imagen';

        $detalle[] = 'Hoja 1:';
        $sheet = $spreadsheet->setActiveSheetIndex(0);

        for ($j = 1; $j <= 3; $j++) {
            [$letraCod, $letraVal, $letraCos] = $this->columnasHoja1($j);
            $this->procesarColumna($detalle, $sheet, 5, 150, $letraCod, $letraVal, $letraCos);
        }

        $detalle[] = 'Hoja 2 Lentes de Contacto: ';
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        for ($j = 1; $j <= 2; $j++) {
            [$letraCod, $letraVal, $letraCos] = $this->columnasLC($j);
            $this->procesarColumna($detalle, $sheet, 5, 34, $letraCod, $letraVal, $letraCos, 'LC');
        }

        $detalle[] = 'Hoja 3 X Serie: ';
        $sheet = $spreadsheet->setActiveSheetIndex(2);
        for ($j = 1; $j <= 2; $j++) {
            [$letraCod, $letraVal, $letraCos] = $this->columnasXSerie($j);
            $this->procesarColumna($detalle, $sheet, 5, 16, $letraCod, $letraVal, $letraCos);
        }

        $detalle[] = 'Hoja 4 Varilux  (No proceso): ';
        $detalle[] = 'Hoja  5 Varilux Comfort: ';
        $sheet = $spreadsheet->setActiveSheetIndex(4);
        for ($j = 1; $j <= 3; $j++) {
            [$letraCod, $letraVal, $letraCos] = $this->columnasVarilux($j);
            $this->procesarColumna($detalle, $sheet, 5, 15, $letraCod, $letraVal, $letraCos);
        }

        $detalle[] = 'Hoja 6 Varilux Physio: ';
        $sheet = $spreadsheet->setActiveSheetIndex(5);
        for ($j = 1; $j <= 3; $j++) {
            [$letraCod, $letraVal, $letraCos] = $this->columnasVarilux($j);
            $this->procesarColumna($detalle, $sheet, 5, 15, $letraCod, $letraVal, $letraCos);
        }


        if ($request->actualiza === 'SI') {
            DB::commit();
            $detalle[] = ' Actualizo BD !!';
        } else {
            DB::rollBack();
            $detalle[] = ' Simulación No Actualizo BD !!';
        }

        return view('mensaje', [
            'titulo' => 'Procesado',
            'detalles' => $detalle,
        ]);
    }

    private function columnasHoja1(int $j): array
    {
        return match ($j) {
            1 => ['Q', 'B', 'G'],
            2 => ['R', 'C', 'H'],
            3 => ['S', 'D', 'I'],
        };
    }

    private function columnasLC(int $j): array
    {
        return match ($j) {
            1 => ['Q', 'D', 'G'],
            2 => ['R', 'E', 'H'],
        };
    }

    private function columnasLC2(int $j): array
    {
        return match ($j) {
            1 => ['X', 'B', 'G'],
            2 => ['Y', 'C', 'H'],
        };
    }

    private function columnasXSerie(int $j): array
    {
        return match ($j) {
            1 => ['N', 'B', 'F'],
            2 => ['O', 'C', 'G'],
        };
    }

    private function columnasVarilux(int $j): array
    {
        return match ($j) {
            1 => ['L', 'D', 'I'],
            2 => ['K', 'C', 'H'],
            3 => ['J', 'B', 'G'],
        };
    }

    private function columnasLab(int $j): array
    {
        return match ($j) {
            1 => ['T', 'D', 'G'],
            2 => ['U', 'E', 'H'],
        };
    }

    private function columnasKodak(int $j): array
    {
        return match ($j) {
            1 => ['Q', 'B', 'G'],
            2 => ['R', 'C', 'H'],
            3 => ['S', 'D', 'I'],
        };
    }

    private function procesarColumna(array &$detalle, Worksheet $sheet, int $filaini, int $filafin, string $letraCod, string $letraVal, string $letraCos, string $familia = 'LEN'): void
    {
        for ($i = $filaini; $i <= $filafin; $i++) {
            $codigo = trim((string) $sheet->getCell($letraCod . $i));
            if ($codigo === '') {
                continue;
            }

            $precio = $sheet->getCell($letraVal . $i)->getCalculatedValue();
            $costo = $sheet->getCell($letraCos . $i)->getCalculatedValue();
            $accion = '';
            $productoNombre = '';

            if (! is_numeric($costo)) {
                $detalle[] = '..' . $codigo . '  Error: Dato Costo ' . $costo;
                $accion = 'Error: De dato Costo';
                $costo = 0;
            }

            if (! is_numeric($precio)) {
                $detalle[] = '..' . $codigo . '  Error: Dato Precio ' . $precio;
                $accion = 'Error: De dato Precio';
                $precio2 = 0;
            } else {
                $precio2 = $precio * 0.9;

                $oproducto = producto::findCodigo($familia, $codigo);
                if (! $oproducto) {
                    $detalle[] = '..' . $codigo . '  Error: No se encontro en tabla Productos';
                    return;
                }

                $oproducto->Prod_Precio = $precio;
                $oproducto->Prod_Precio2 = $precio2;
                $oproducto->Prod_Costo = $costo;
                $oproducto->Prod_UsuUltMan = 'PrecioExcel';
                $productoNombre = $oproducto->Prod_Descripcion;

                if ($oproducto->actualizar() && $oproducto->indicadorModifico) {
                    $accion = ' ACTUALIZADO ';
                }
            }

            $detalle[] = '..' . $codigo . '  ' . $productoNombre . '    Precio: ' . $precio . ' Precio2: ' . ($precio2 ?? 0) . ' Costo:' . $costo . ' ' . $accion;
        }
    }
}
