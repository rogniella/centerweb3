<?php

namespace App\Actions\Precios;

use App\clases\comprobante;

class ConsultarPrecioVentaAction
{
    public function __invoke(): void
    {
        $ocomprobante = new comprobante;
        $ocomprobante->comp_tipoot = 'CA';
        $ocomprobante->comp_responsable = auth()->user()?->name ?? '';
        $ocomprobante->comp_fecmov = date('Y-m-d');

        $ocomprobante->linea_detalle[0]['familia'] = request()['familia'];
        $ocomprobante->linea_detalle[0]['codigo'] = request()['codigo'];
        $ocomprobante->linea_detalle[0]['detalle'] = '';
        $ocomprobante->linea_detalle[0]['cantidad'] = 1;
        $ocomprobante->linea_detalle[0]['precio'] = request()['monto'];
        $ocomprobante->linea_detalle[0]['tipoiva'] = 1;

        if (request()['descuento'] > 0) {
            $ocomprobante->linea_detalle[1]['familia'] = 'VAR';
            $ocomprobante->linea_detalle[1]['codigo'] = request()['familia'] === 'REC' ? '0329' : '0349';
            $ocomprobante->linea_detalle[1]['detalle'] = 'Descuento';
            $ocomprobante->linea_detalle[1]['cantidad'] = 1;
            $ocomprobante->linea_detalle[1]['precio'] = request()['descuento'] * -1;
            $ocomprobante->linea_detalle[1]['tipoiva'] = 1;
        }

        $ocomprobante->nuevo();

        if ($ocomprobante->ret !== '') {
            displaylog('Error en ConsultaPrecioVenta: Al generar NuevoComprobante');
            echo $ocomprobante->ret;
            exit;
        }

        echo json_encode(['html' => 'Ok']);
    }
}
