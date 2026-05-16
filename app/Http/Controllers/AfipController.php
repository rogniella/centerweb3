<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//  Para que funcione ver documentacion del componete composer require afipsdk/afip.php
use App\clases\comprobante;

use App\Models\proveedor;  // Modelos a utilizar
use App\Models\comprasProv;  // Modelos a utilizar

use Laracasts\Flash\Flash;
//Ver, por falla en  $sheet->getCell($letraVal.$i)->getCalculatedValue()  tuve que copiar la carpeta que me bajo en vendor, por la que funcionaba Ok , esta en el d:/tools/web/php y excel/ zip . 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Models\movimiento_tarjeta;  // Modelos a utilizar

use App\clases\ws_afip;


class AfipController extends Controller
{

  public function consulta_factura (Request $request)  {

    /**
        Entrada:
        * Tipo de comprobante  
             1 = Factura A
             3 = Nota de Crédito A   
             6 = Factura B
             8 = Nota de Crédito B
            11 = Factura C
            13 = Nota de Crédito C

        * Numero del punto de venta
        * Numero de factura
    **/

    $comprobante   = Comprobante::find($request->sucursal, $request->tipo, $request->id);
    if( $comprobante->ret != "") {
      dd($comprobante->ret);
    }  

    $punto_de_venta = $comprobante->punto_de_venta ;

    $datos = ws_afip::consulta_comprobante ($comprobante->numero_de_factura, $punto_de_venta, $comprobante->tipo_de_factura);

    dd( $datos['msgError'], $datos ['informacion']  ,  $datos ['informacion']->ImpTotal);


    return response()->json([
        'informacion' =>  $informacion,
        'msgError' => $msgError  
    ]);

  } // Fin consulta_factura    

  public function valida_estado_servidor ()  {

  	 /* Retorna Json con:
         msgError 
         informacion	
     */     
     return ws_afip::valida_estado_servidor ( );

  } // Fin valida_estado   

  public function valida_cuit (Request $request)  {

  	// Entrada	
  	 $cuit = (double) $request->cuit;
  	 /* Retorna Json con:
        $msgError 
        $direccion	
        $razonSocial
     */     
     return  ws_afip::valida_cuit ( $cuit );
    
  } // Fin validate_cuit

  public function carga_comp_recibido_afip ()  {
     // Pantalla , archivo a procesar
     return view('procesa_archivo' ,  ['titulo' => "Proceso de Coprobantes Recibidos en AFIP",
                                       'mensaje' => "Seleccione Archivo Comprobante Recibidos de Pagina AFIP a Procesar",
                                       'accion' =>  "carga_comp_recibido_afip2",
                                       'tipoArchivo' => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"  ] );

  } // Fin carga_comp_recibido_afip


  public function carga_comp_recibido_afipTarjeta (Request $request)  {

    // Procesa Archivo seleccionado, con los datos Ventas con Tarjetas Creditos

    $file = $request->file("nombre_archivo");

   $archivo = fopen($file,"rb");
    while( feof($archivo) == false )
     {
       $linea = fgets($archivo);
       $tipoRegistro = substr($linea, 0, 1);
       if( $tipoRegistro == '36') {
         echo $tipoRegistro .  " " . $linea . "<br />";
       }       
       if( $tipoRegistro == '3') {
         $mov = new movimiento_tarjeta;


         $mov->producto = substr($linea, 15, 1);
         $fecha = substr($linea, 61, 8);
         $mov->fecha_operacion = date("Ymd", strtotime($fecha));
         $mov->plan_cuotas = substr($linea, 99, 2);
         $mov->importe_total = substr($linea, 103, 13);
         $mov->importe_total = $mov->importe_total / 100;
         $mov->sucursal = substr($linea, 82, 9);
         $mov->lote = substr($linea, 91, 3);
         $mov->cupon = substr($linea, 94, 5);
         $mov->tipo_plan = substr($linea, 151, 1);
         $mov->plan_cuotas = substr($linea, 99, 2);
         $mov->marca_error = substr($linea, 150, 1); // 0= Ok  1=Rechazo
         $mov->porc_descuento = substr($linea, 145, 5);
         $mov->porc_descuento = $mov->porc_descuento / 100;

         $mov->importe_arancel = substr($linea, 202, 9);
         $mov->importe_arancel = $mov->importe_arancel / 100;
         $mov->iva_arancel = substr($linea, 212, 9);
         $mov->iva_arancel = $mov->iva_arancel / 100;  // Arancel total
         $mov->importe_financ  = substr($linea, 228, 9);
         $mov->importe_financ  = $mov->importe_financ  / 100;
         $mov->iva_financ  = substr($linea, 238, 9);
         $mov->iva_financ  = $mov->iva_financ  / 100;

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


//         echo $tipoRegistro .  " " . $linea . "<br />";
       //  echo $terminal .  " " . $producto .  " " . $fecOperacion .  " " . $cuotas . " " . $importe ;
         echo " "  . $impCosto  ;
    //     echo  " " .$marcaError. " "  . $porcDescuento . " " . $lote . "-" . $cupon . " " . $tipoPlan . "<br />";

         $mov->save();

       }
     }
    fclose($archivo);

    dd($archivo);

  } // Fin carga_comp_recibido_afip

  public function carga_comp_recibido_afip2 (Request $request)  {

    // Procesa Archivo seleccionado, con los datos de AFIP Comp Recibidos

    $file = $request->file("nombre_archivo");
    //obtenemos el nombre del archivo
    //$nombre = $file->getClientOriginalName();

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $spreadsheet = $reader->load($file);   
    $sheet = $spreadsheet->setActiveSheetIndex(0); 

    // Valido el Archivo
    if (trim($sheet->getCell('A1')) != "Mis Comprobantes Recibidos - CUIT 27233589611"){
       Flash::error("ERROR: Archivo no es del tipo: AFIP-Comprobantes Recibidos" );     
       return view('procesa_archivo' ,  ['titulo' => "Proceso de Coprobantes Recibidos en AFIP",
                              'mensaje' => "Seleccione Archivo a Procesar"  ] );  
    }

    $detalle  = [];

    // Recorro las filas que tiene datos
    $suma_total = 0;
    $cant_total = 0;
    $cant_alta = 0;
    for ($i = 3 ; $i <= 500; $i++) 
    {
      $fecha =  trim($sheet->getCell('A'.$i)); //Retorna valor
      if ($fecha <> "") {
          $error =  "";
          $tipo =  trim($sheet->getCell('B'.$i)); 
          $punto_de_venta =  trim($sheet->getCell('C'.$i));
          $numero_de_factura =  trim($sheet->getCell('D'.$i));
          $cuit =  trim($sheet->getCell('H'.$i));
          $prov_raz_social =  trim($sheet->getCell('I'.$i));
          $neto =  trim($sheet->getCell('L'.$i));
          $neto_no_grav =  trim($sheet->getCell('M'.$i));
          $exenta =  trim($sheet->getCell('N'.$i));
          $iva =  trim($sheet->getCell('O'.$i));
          $total =  trim($sheet->getCell('P'.$i));

          $suma_total += $total;
          $cant_total ++ ;
          $datos = proveedor::buscar("",$cuit,1);
          if (isset( $datos[0] )) {
              $prov =  $datos[0]->prov_razsocial;
              $prov_id = $datos[0]->prov_id;
              $ctacon = $datos[0]->prov_ctacon;
          }else{
            $prov = 'Prov Nuevo';
            $proveedor = new proveedor;
            $proveedor->Prov_RazSocial = $prov_raz_social; 
            $proveedor->Prov_NomFant = $prov_raz_social; 
            $proveedor->Prov_Cuit = $cuit; 
            $proveedor->save();               
            $prov_id = $proveedor->Prov_id;
            $ctacon = 0;
            $detalle[] =  $tipo  . " " . $prov . " => " . $prov_raz_social ;   
          } 
          $tipo_comp = $this->ConvTipoCompNro_Letra( substr ($tipo , 0,2) ) ;

          // Grabo tabla de Compras Prov
          $compra = New comprasProv;
          $compra->Cprov_IdProv = $prov_id;
          $compra->Cprov_RazonSocial = $prov_raz_social;
          $compra->Cprov_CUIT = $cuit;
          $compra->Cprov_Fecha = $fecha;
          $compra->Cprov_Comprobante = $tipo_comp;
          $compra->Cprov_NroPuntoVta = sprintf("%'.04d\n", $punto_de_venta ) ;
          $compra->Cprov_NroFactura = sprintf("%'.08d\n",$numero_de_factura) ;
          $compra->Cprov_Subtot = numdec($neto,2);
          $compra->Cprov_NoGravado = 0;
          $compra->Cprov_TasaIvaIns = numdec( 0.21 ,2);
          $compra->Cprov_TasaIvaIns1 = numdec(0.105,2);
          $compra->Cprov_TasaIvaIns2  = numdec(0.27,2);
          $compra->Cprov_IVAInscrip  = numdec($iva,2);
          $compra->Cprov_IVAInscrip1  = 0;
          $compra->Cprov_IVAInscrip2  = 0;
          $compra->Cprov_IVANoInscrip = 0; 
          $compra->Cprov_PercRec  = 0;
          $compra->Cprov_Ret  = 0;
          $compra->Cprov_PercIB  = 0;
          $compra->Cprov_ImpInt = 0; 
          $compra->Cprov_CtaCon = $ctacon; 
          $compra->Cprov_Estado = 'M'; // Migrado AFIP 
          $compra->Cprov_UsuUltMan = 'AFIP';
          $compra->Cprov_PerContable = 0;
          $compra->Cprov_Subtot1 = 0;
          $compra->Cprov_Subtot2 = 0;
          $compra->Cprov_Total = numdec($total,2);
          try {
            $compra->save();
            $cant_alta ++ ;
          } catch (\Exception $e) {
//            dd($e,$compra, $e->errorInfo[2]);
            if ( $e->errorInfo[1] == -1605 )  {
              // Duplicado
              $error =  "DUPLICADO";
            }else{
              $error =  convert_from_latin1_to_utf8_recursively($e->getMessage() );
            }  

            $detalle[] = $tipo_comp . " " . $tipo  . " " . $numero_de_factura . " " . $prov . " " . $error ;     
          } 
      } // End datos
    }  //  End For     

    $detalle[] =  " Cantidad de Comprobantes = " . $cant_total  ;
    $detalle[] =  " Cantidad de Altas de Proveedores = " . $cant_alta ;
    $detalle[] =  " Monto Total =  $ " . $suma_total ;

    return view('mensaje', ['titulo' => "Procesado",
                           'detalles' => $detalle ] );

  } // Fin carga_comp_recibido_afip2


  private function ConvTipoCompNro_Letra( $tipoNro){
 
    // Retorna Cod Afa Segun Afip
    switch ($tipoNro ) {
      case "1 ": // Factura A
        return "A" ;  
        break;
      case "2 ": // Nota Deb A
        return "D" ;  
        break;
      case "3 ": // Nota Cred A
        return "R" ;  
        break;
      case "6 ": // Factura B
        return "B" ;  
        break;
      case "8 ": // Nota Cred B
        return "S" ;  
        break;
      case "11": // Factura C
        return "C" ;  
        break;
      case "12": // Nota Debito C
        return "N" ;  
        break; 
      case "13": // Nota Credito C
        return "P" ;  
        break;
      case "51": // Factura M
        return "M" ;  
        break;
      default:
          return $tipoNro;
          break;  
    }        
  }


}
