<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

use App\Models\factura;
use App\clases\comprobante;

class FacturasController extends Controller
{
    
  public function index()
  {
      // Lista de Facturas (Comprobantes ante Afip) - Pantalla Principal
      return view('facturas.index');
  }

  public function buscar(Request $request)
  {
      // Buscar de la Pantalla Principal
      if($request->ajax() ) {
        $datos = factura::listar($request->filtro0, $request->filtro1, $request->filtro2, $request->fecha,  $request->fechafin ,  10000);

        foreach ($datos as $fila) {
            $fila->Fac_Total = number_format($fila->Fac_Total,2,".","");
        }  
   
        return response()->json([ 'results' => $datos ]);
      }  // Fin Ajax
  } // Fin Buscar


  public function delete(Request $request)
  {

    $registro = Factura::find( $request->id); // Accedo por idWEB

    // Dependiendo del estado, la elimino o se Anula y genera Nota Debito
    if($registro->Fac_Estado == "E" ) {
        // Para Anular leer el comprobante original 
        // A partir de este generar uno nuevo inverso
        // Cambiar el estado del comprobante Original
        $mensaje = 'El Comprobante Fiscal ha sido Anulado de forma exitosa';
    }else{
        $registro->delete();
        $mensaje = 'El Comprobante Fiscal ha sido borrado de forma exitosa';
    }


    return response()->json([
      'ret' => $mensaje
    ]);

  }

} // Fin de la Clase