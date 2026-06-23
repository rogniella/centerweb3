<?php

namespace App\Actions\Cristales;

use App\Models\inventario;
use App\Models\producto;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class GenerarPlanillaStockAction
{
    public function __invoke(): View
    {
        $file1 = $this->generarPlanillaSucursal(1);
        $file2 = $this->generarPlanillaSucursal(2);

        $fecha = date('Y-m-d');

        $filezipname = 'StockCristales_' . $fecha . '.zip';
        $filezip = '/salidas/' . $filezipname;
        $filenameSalidaZip = public_path($filezip);

        $zip = new ZipArchive;
        if ($zip->open($filenameSalidaZip, ZipArchive::CREATE) !== true) {
            throw new \Exception("No se pudo crear el archivo ZIP: $filenameSalidaZip (estado: {$zip->status})");
        }
        $zip->addFile(public_path('salidas/' . $file2), $file2);
        $zip->addFile(public_path('salidas/' . $file1), $file1);
        $zip->close();

        $fileRedirec = asset($filezip);

        return view('mensaje', [
            'titulo' => 'CONFIRMACIÓN',
            'mensaje' => 'Se generaron Planillas de Stock Cristales por Sucursal',
            'pdf' => $fileRedirec,
        ]);
    }

    private function generarPlanillaSucursal(int $sucursal): string
    {
        $filename = storage_path() . '/template/PlanillaCristales.xlsx';

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($filename);
        $sheet = $spreadsheet->setActiveSheetIndex(0);

        $codMaterialAnterior = '';

        $sheet->setCellValue('R2', date('Y-m-d H:i:s'));

        $datos = producto::listar('CRI', '', 1600, '', '');

        foreach ($datos as $elem) {
            if ($elem->prod_id === '9999' || $elem->prod_id === '0' || $elem->prod_usualta === '') {
                continue;
            }

            $codMaterial = substr($elem->prod_id, 0, 2);
            if ($codMaterial !== $codMaterialAnterior) {
                $sheet = match ($codMaterial) {
                    'OB' => $spreadsheet->setActiveSheetIndex(0),
                    'OR' => $spreadsheet->setActiveSheetIndex(1),
                    'OA' => $spreadsheet->setActiveSheetIndex(2),
                    default => $sheet,
                };
                $codMaterialAnterior = $codMaterial;
            }

            $celda = $elem->prod_usualta;
            $valor = 0;
            if ($inv = inventario::findCodigo($elem->prod_idweb, $sucursal)) {
                $valor = $inv->Inv_Stock;
            }

            try {
                $sheet->setCellValue($celda, $valor);
            } catch (\Exception $e) {
                displaylog('Error en producto ' . $elem->prod_id . ' Celda ' . $elem->prod_usualta . ' Error:' . $e);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $file = 'StockCristales_' . $sucursal . '.xlsx';
        $filenameSalida = public_path('/salidas/' . $file);
        $writer->save($filenameSalida);

        return $file;
    }
}
