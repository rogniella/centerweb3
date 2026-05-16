<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\tar_operacion;
use App\Models\tar_liquidacion;
use App\Models\tar_producto;

class TarjetasController extends Controller
{
    public function index()
    {
        // Vista que pide los archivos bajados de la aplicacion de las tarjetas  a subir
        return view('tarjetas.index');
    }

    public function upload(Request $request)
    {
        // Procesa los acrhivos bajados del portal de las tarjetas

        $request->validate([
            'archivos' => 'required|array',
            'archivos.*' => 'file|mimes:txt,xls|max:10240',
        ]);

        $tarjetasPath = storage_path('tarjetas');

        if (!is_dir($tarjetasPath)) {
            mkdir($tarjetasPath, 0755, true);
        }

        $contador = 0;
        foreach ($request->file('archivos') as $archivo) {
            $nombre = time() . '_' . $archivo->getClientOriginalName();
            $archivo->move($tarjetasPath, $nombre);
            $contador++;
        }

        // Comienzo el Proceso de Actualizacion de las tablas de Tarjetas
        $cantidad_leidos = 0;
        $archivos = \File::files($tarjetasPath); //trae solo archivos
        foreach ($archivos as $file) {
            $archivo = fopen($file,"rb");
            while( feof($archivo) == false )
                {
                $linea = fgets($archivo);
                $this->proceso_linea_tarjeta( $linea);
            }
            fclose($archivo);
            $cantidad_leidos = $cantidad_leidos + 1;
        }; // fin foreach 




        flash("Se subieron $contador archivo(s) correctamente. Procesados: $cantidad_leidos")->success();

        return redirect()->route('tarjetas.index');
    }

    private function proceso_linea_tarjeta( $linea){

       $tipoRegistro = substr($linea, 0, 1);

         $idliq = substr($linea, 54, 7);
        /*
         if ($idliq == 310778) {
            printf(' linea:');
            printf($linea);
         }
        */

       if( $tipoRegistro == '7') {
         // Trailer Liquidacion (parte 1) 
         $mov = new tar_liquidacion;
         $mov->idliquidacion = substr($linea, 54, 7);

         $mov->producto = substr($linea, 15, 1);
         $mov->moneda = substr($linea, 16, 3);
         $mov->plazo_pago = substr($linea, 21, 2);
         $fecha = substr($linea, 24, 8);
         $mov->fecha_presentacion = date("Ymd", strtotime($fecha));


         $fecha = substr($linea, 32, 8);
         $mov->fecha_clearing = date("Ymd", strtotime($fecha));

         $mov->mto_bruto = substr($linea, 61, 13);
         $mov->mto_bruto = $mov->mto_bruto / 100;
         $mov->mto_sindto = substr($linea, 75, 13);
         $mov->mto_sindto = $mov->mto_sindto / 100;
         $mov->mto_final = substr($linea, 89, 13);
         $mov->mto_final = $mov->mto_final / 100;
         $mov->mto_neto = substr($linea, 159, 13);
         $mov->mto_neto = $mov->mto_neto / 100;
//         $mov->mto_arancel = substr($linea, 103, 13);  // Aranceles más Costos Financieros.
//         $mov->mto_arancel = $mov->mto_arancel / 100;
         $mov->cant_operaciones = substr($linea, 173, 7);

         $mov->mto_otros_deb = substr($linea, 131, 13);
         $mov->mto_otros_deb = $mov->mto_otros_deb / 100;
         $mov->mto_otros_cre = substr($linea, 145, 13);
         $mov->mto_otros_cre = $mov->mto_otros_cre / 100;

         $mov->save();

       }

       if( $tipoRegistro == '8') {
         // Trailer Liquidacion (parte 2) 

         $idliquidacion = substr($linea, 54, 7);
         $mov = tar_liquidacion::findIdLiquidacion($idliquidacion);

         $mov->iva_arancel = substr($linea, 63, 13);
         $mov->iva_arancel = $mov->iva_arancel / 100;

         $mov->impuesto_debcred = substr($linea, 77, 13);
         $mov->impuesto_debcred = $mov->impuesto_debcred / 100;

         $mov->iva_anticipo = substr($linea, 91, 13);
         $mov->iva_anticipo = $mov->iva_anticipo / 100;

         $mov->retiva_ventas = substr($linea, 105, 13);
         $mov->retiva_ventas = $mov->retiva_ventas / 100;

         $mov->percep_iva = substr($linea, 119, 13);
         $mov->percep_iva = $mov->percep_iva / 100;

         $mov->ret_ganancias = substr($linea, 133, 13);
         $mov->ret_ganancias = $mov->ret_ganancias / 100;

         $mov->ret_ib = substr($linea, 147, 13);
         $mov->ret_ib = $mov->ret_ib / 100;

         $mov->percep_ib = substr($linea, 161, 13);
         $mov->percep_ib = $mov->percep_ib / 100;

         $mov->mto_arancel = substr($linea, 200, 11);  // Aranceles más Costos Financieros.
         $mov->mto_arancel = $mov->mto_arancel / 100;

         $mov->costo_financiero = substr($linea, 212, 11);
         $mov->costo_financiero = $mov->costo_financiero / 100;

         $mov->iva_impinteres = substr($linea, 190, 9);
         $mov->iva_impinteres = $mov->iva_impinteres / 100;


         if ( $mov->plazo_pago == 10 ){ // Es ahora 12
                $interes =  round($mov->mto_otros_deb / 1.105 , 2);
                $mov->costo_financiero = $mov->costo_financiero + $interes;
                $mov->iva_anticipo = $mov->iva_anticipo +  round($interes * 0.105 ,2);
                $mov->mto_otros_deb = 0;
                $mov->observacion = 'Ahora 12 ';
         }
 
         $resul =  $mov->mto_bruto - $mov->mto_arancel -  $mov->costo_financiero -  $mov->iva_arancel - $mov->iva_anticipo - $mov->ret_ib - $mov->mto_otros_deb - $mov->percep_iva;
         $diferencia = round($resul) - round($mov->mto_neto) ; //ignoro dif de centavos 
          if ( !$diferencia == 0 ) {
                $mov->observacion =  $mov->observacion . 'Diferencia ' . $diferencia;
         }

         if ( $mov->plazo_pago == 18 ){ // Es Promo BcoCtes
                // Calcular el real presentado
                // $mov->mto_bruto = //;
                $mov->observacion = $mov->observacion .'Promo BcoCo';
         }

         $mov->save();

       }


       if( $tipoRegistro == '3') {
         $mov = new tar_operacion;

         $mov->terminal = substr($linea, 82, 9);
         $mov->lote = substr($linea, 91, 3);
         $mov->cupon = substr($linea, 94, 5);

         $mov->producto = substr($linea, 15, 1);
         $mov->moneda = substr($linea, 16, 3);
         $mov->plazo_pago = substr($linea, 21, 2);
         $mov->idliquidacion = substr($linea, 54, 7);

         $fecha = substr($linea, 32, 8);
         $mov->fecha_clearing    = date("Ymd", strtotime($fecha));
         $fecha = substr($linea, 61, 8);
         $mov->fecha_operacion = date("Ymd", strtotime($fecha));
         $fecha = substr($linea, 24, 8);
         $mov->fecha_presentacion = date("Ymd", strtotime($fecha));
         $mov->cod_movimiento = substr($linea, 69, 3);

         $mov->cuotas = substr($linea, 99, 2);
         $mov->mto_bruto = substr($linea, 103, 13);
         $mov->mto_bruto = $mov->mto_bruto / 100;
         $mov->mto_sindto = substr($linea, 117, 13);
         $mov->mto_sindto = $mov->mto_sindto / 100;

         $mov->mto_final = substr($linea, 117, 13);
         $mov->mto_final = $mov->mto_final / 100;

         $mov->mto_arancel = substr($linea, 202, 9);
         $mov->mto_arancel = $mov->mto_arancel / 100;
         $mov->iva_arancel = substr($linea, 212, 9);
         $mov->iva_arancel = $mov->iva_arancel / 100;  // Arancel total
         $mov->mto_financiero  = substr($linea, 228, 9);
         $mov->mto_financiero  = $mov->mto_financiero  / 100;
         $mov->iva_financiero  = substr($linea, 238, 9);
         $mov->iva_financiero  = $mov->iva_financiero  / 100;
         $mov->marca_error = substr($linea, 150, 1); // 0= Ok  1=Rechazo
         $mov->porc_descuento = substr($linea, 145, 5);
         $mov->porc_descuento = $mov->porc_descuento / 100;
 
  /*     


         $mov->tasa_cuotas = substr($linea, 223, 5);
         $mov->tasa_cuotas = $mov->tasa_cuotas / 100;

         $mov->tasa_directa = substr($linea, 248, 5);
         $mov->tasa_directa = $mov->tasa_directa / 100;
         $mov->importe_directa = substr($linea, 253, 9);
         $mov->importe_directa = $mov->importe_directa / 100;
         $mov->iva_directa = substr($linea, 263, 9);
         $mov->iva_directa = $mov->iva_directa   / 100;

         $impCosto   = substr($linea, 281, 5);
         $impCosto   = $impCosto / 100;

         $mov->importe_rentas = $mov->importe_total * 0.02;
         $mov->importe_depositado  = $mov->importe_total - $mov->importe_arancel  - $mov->iva_arancel  - 
            $mov->importe_financ  - $mov->iva_financ  -  $mov->iva_directa  - $mov->importe_rentas   ;
         $mov->importe_final = 0; // $mov->importe_depositado - imp_bancos;

*/
         $mov->save();

       }

    } // Fin Procesa Linea




  public function lista_operaciones ()  {

      $productos= tar_producto::orderBy('id')->pluck( 'descripcion','id');       
      return view('tarjetas.index_operaciones', [ 'productos' => $productos] );

  } // Fin lista 

  public function buscar_operaciones(Request $request)
  {
      // Boton de la vista lista movimientos
        $datos = tar_operacion::listar($request->filtro0, $request->filtro2, $request->fecha,  $request->fechafin ,  10000);
   
        return response()->json([ 'results' => $datos ]);

  } // Fin Buscar  


  public function lista_liquidaciones ()  {

      $productos= tar_producto::orderBy('id')->pluck( 'descripcion','id');       
      
 
      return view('tarjetas.index_liquidaciones', [ 'productos' => $productos] );

  } // Fin lista 

  public function buscar_liquidaciones(Request $request)
  {
      // Boton de la vista lista liquidaciones


      if($request->ajax() ) {
        $datos = tar_liquidacion::listar($request->filtro0, $request->filtro2, $request->fecha,  $request->fechafin ,  10000);

  //      foreach ($datos as $fila) {
  //          $fila->Fac_Total = number_format($fila->Fac_Total,2,".","");
  //      }  
   
        return response()->json([ 'results' => $datos ]);
      }  // Fin Ajax

  } // Fin Buscar  

}
