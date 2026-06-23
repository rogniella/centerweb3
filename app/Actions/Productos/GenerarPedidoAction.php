<?php

namespace App\Actions\Productos;

use App\Models\marca;
use App\Models\producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class GenerarPedidoAction
{
    public function __invoke(Request $request): JsonResponse
    {
        $filename = storage_path() . '/template/PlanillaCristales.xlsx';
        $filename2 = storage_path() . '/template/PlanillaPedidos.xlsx';

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($filename);
        $sheet = $spreadsheet->setActiveSheetIndex(0);

        $reader3 = IOFactory::createReader('Xlsx');
        $spreadsheet3 = $reader3->load($filename);
        $sheet3 = $spreadsheet3->setActiveSheetIndex(0);

        $reader2 = IOFactory::createReader('Xlsx');
        $spreadsheet2 = $reader2->load($filename2);
        $sheet2 = $spreadsheet2->setActiveSheetIndex(0);
        $fila = 3;

        $meses = $request->mes_ventas ?: 2;
        $filtroFecini = date('Y-m-d', strtotime('-' . $meses . ' month')) . ' 00:00:00';

        $datos = producto::listar(
            $request->filtro_flia,
            $request->filtro1,
            10000,
            $request->filtro2,
            $request->filtro3,
            $request->filtroEstado,
            $request->filtroMarca
        );

        foreach ($datos as $row) {
            if ($row->prod_marca != 0) {
                $marcaObj = marca::find($row->prod_marca);
                $row->prod_marca2 = $marcaObj?->nombre ?? '';
            } else {
                $row->prod_marca2 = '';
            }

            $dat = DB::select(
                "SELECT SUM(Mov_Cantidad) * -1 as cantidad FROM moviproductos WHERE Mov_Familia=? AND Mov_IdProd=? AND Mov_FecMov>=? AND (Mov_Operacion='V' OR Mov_Operacion='R') AND Mov_Sucursal=?",
                [$row->prod_familia, $row->prod_id, $filtroFecini, 1]
            );
            $row->venta01 = $dat[0]->cantidad;

            $dat = DB::select(
                "SELECT SUM(Mov_Cantidad) * -1 as cantidad FROM moviproductos WHERE Mov_Familia=? AND Mov_IdProd=? AND Mov_FecMov>=? AND (Mov_Operacion='V' OR Mov_Operacion='R') AND Mov_Sucursal=?",
                [$row->prod_familia, $row->prod_id, $filtroFecini, 2]
            );
            $row->venta02 = $dat[0]->cantidad;

            if (trim($row->prod_familia) === 'CRI') {
                $sheet3->setCellValue($row->prod_usualta, $row->venta01 + $row->venta02);
            }

            $stockTotal = $row->stock01 + $row->stock02;
            $ventasTotal = $row->venta01 + $row->venta02;
            $diferencia = $ventasTotal - $stockTotal;

            if ($diferencia > 0) {
                displaylog($diferencia . ' ' . $row->prod_familia);

                if (trim($row->prod_familia) === 'CRI') {
                    $sheet->setCellValue($row->prod_usualta, $diferencia);
                    displaylog($diferencia . ' planilla ' . $row->prod_usualta);
                }

                $fila++;
                $sheet2->setCellValue('A' . $fila, $row->prod_familia . '-' . $row->prod_id);
                $sheet2->setCellValue('B' . $fila, $row->prod_descripcion);
                $sheet2->setCellValue('C' . $fila, $diferencia);
            }
        }

        $fecha = date('Y-m-d');

        $writer = new Xlsx($spreadsheet);
        $path1 = 'salidas/PedidoCristales_' . $fecha . '.xlsx';
        $writer->save(public_path($path1));

        $writer2 = new Xlsx($spreadsheet2);
        $path2 = 'salidas/PedidoProveedor_' . $fecha . '.xlsx';
        $writer2->save(public_path($path2));

        $writer3 = new Xlsx($spreadsheet3);
        $path3 = 'salidas/VentasCristales_' . $fecha . '.xlsx';
        $writer3->save(public_path($path3));

        $filezipname = 'Pedido_' . $fecha . '.zip';
        $filezip = '/salidas/' . $filezipname;
        $filenameSalidaZip = public_path($filezip);

        $zip = new ZipArchive;
        if ($zip->open($filenameSalidaZip, ZipArchive::CREATE) !== true) {
            throw new \Exception("No se pudo crear el archivo ZIP: $filenameSalidaZip (estado: {$zip->status})");
        }
        $zip->addFile(public_path($path3), basename($path3));
        $zip->addFile(public_path($path2), basename($path2));
        $zip->addFile(public_path($path1), basename($path1));
        $zip->close();

        return response()->json(['redirec' => asset($filezip)]);
    }
}
