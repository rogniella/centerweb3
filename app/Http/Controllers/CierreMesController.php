<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\CierreMensual_valor;
use App\producto;  


class CierreMesController extends Controller
{

    public function index()
    {     
        // Pantalla Principal de consulta
        return view('cierre_mes.index');
    } 


    public function proceso()
    {     

      // Se ejecuta automaticamente desde Aplicacion Vb cada inicio de Mes
      // Tambien se lo puede re-procesar  desde la Web Cierres por cualquier cosa
      // desde la pantalla de consulta de Cierres

      $periodo=$_GET['periodo'];
      $anio = substr($periodo,0,4);
      $mes = substr($periodo,4,2);

      $mensaje = ''; //De salida , informativo

      // Migro Facturacion del mes si no es casa Central => Las sucursales Informan sus Facturas
      if ( env('SUCURSAL_LOCAL') != '01') {
        $consulta = "SELECT * FROM Facturas WHERE year(fac_fecha) = $anio and Month(fac_fecha) =  $mes";
        $facturas = DB::connection('bdcomercio')->select($consulta ); 
        $facturas = convert_from_latin1_to_utf8_recursively($facturas );
        // Generar archivo json y lo dejo en carpeta de Intercambio entre Sucursales
        $datos = Array
            (
               "tipo" => 'CIERRE', 
               "idlote" => $periodo, 
               "fecha" =>   date('Ymd'),  //  "20190303" , 
               "observacion" => "Facturacion-CierreMes", 
               "sucursalOrigen" => env('SUCURSAL_LOCAL'), 
               "sucursalDestino" => env('SUCURSAL_ENVIO'), 
               "sucursalOrigenDescri" => env('SUCURSAL_LOCAL') . " - ". env('SUCURSAL_LOCAL_DESCRI'), 
               "sucursalDestinoDescri" => "", 
               "cantidad" => count($facturas), 
               "monto" => 0,
               "facturas" => $facturas 
        );
        $data = json_encode($datos);
        $file =  storage_path() . "/sucursales/" . env('SUCURSAL_ENVIO') . "/facturas_" . env('SUCURSAL_LOCAL') . "_" . $periodo .".json";
        file_put_contents($file, $data);
        //  Guardo copia  
        $file =  storage_path() . "/sucursales/" . env('SUCURSAL_ENVIO') . "/envios/facturas_" . env('SUCURSAL_LOCAL') . "_" . $periodo .".json";
        file_put_contents($file, $data);
        $mensaje = $mensaje  . 'Se genero Informe de Facturación. ' . count($facturas) . '<br>';

      }           
  
      // Borro los datos por si ya proceso periodo
      $consulta = "DELETE FROM cierresmensuales_valores  WHERE periodo = $periodo";
      $resbd = DB::select($consulta );

      $datostabla = [];
      $datostabla[] = array( 'id'  => '99999'); //Crea el elemento cero para que funcione la busqueda correctamente 

      // Familias: Genero un vector con todos las Familias a Controlar
      // --------
      $familias = [ 'REC','SOL','LIQ','CEL','CON','CRI','REL'];
      foreach ($familias as $row) {

          $consulta = "SELECT sum(Prod_Stock) as stock, sum(Prod_StockDep) as Deposito FROM Productos  WHERE    Prod_Familia= '$row' AND Prod_Estado <>'I' and Prod_Id <> '0' and prod_id <> 'par' and prod_id <> 'caja'";
          $resbd = DB::connection('bdcomercio')->select($consulta ); 
          $stock = $resbd[0]->stock; 

          // Busco Stock del periodo Anterior 
          $consulta = "SELECT stock  FROM cierresmensuales_valores  WHERE familia = '$row' AND codigo ='' and periodo < $periodo order by id desc limit 1";
          $resbd = DB::select($consulta );
          if (isset($resbd[0]->stock)) { 
              $stockIni = $resbd[0]->stock; 
          }else{
              $stockIni = 0;
          }      
          $datostabla[] = array(
              'id'  => $row,  
              'familia'  => $row,  
              'idProd'  => '', 
              'stock_ini'  => $stockIni,
              'ventas'  => 0,
              'compras'  => 0,
              'sucursales'  => 0,
              'ajustes'  => 0,
              'ventas_p'  => 0,
              'compras_p'  => 0,
              'stock'  => $stock
          );    
                           
      } // End cursor Familias
  
      // Productos: Genero un vector con todos los productos a Controlar  (Marcados por Stock Min)
      // ----------
      $consulta= "SELECT Prod_Familia,Prod_Id,Prod_Descripcion,Prod_Stock ,Prod_StockMin FROM Productos  WHERE Prod_Familia <> 'LEN'  and  Prod_StockMin > 0 AND Prod_Estado<>'I' ";
      $resbd = DB::connection('bdcomercio')->select($consulta ); 
      $resbd   = convert_from_latin1_to_utf8_recursively($resbd );
      foreach ($resbd as $row) {

          // Busco Stock del periodo Anterior 
            $consulta2 = "SELECT stock  FROM cierresmensuales_valores  WHERE familia = '$row->Prod_Familia' AND codigo ='$row->Prod_Id' and periodo < $periodo order by id desc limit 1";
            $resbd2 = DB::select($consulta2 );
            if (isset($resbd2[0]->stock)) { 
              $stockIni = $resbd2[0]->stock; 
            }else{
              $stockIni = 0;
            }      

             $datostabla[] = array(
              'id'  => trim($row->Prod_Familia) . trim($row->Prod_Id),  
              'familia'  => $row->Prod_Familia,  
              'idProd'  => $row->Prod_Id, 
              'stock_ini'  => $stockIni,
              'ventas'  => 0,
              'compras'  => 0,
              'sucursales'  => 0,
              'ajustes'  => 0,
              'ventas_p'  => 0,
              'compras_p'  => 0,
              'stock'  => $row->Prod_Stock
             );    
                           
      } // End cursor Productos

      // Recorro los Movimentos para completar vector con las ventas , compras, etc
      // ------------  
      $filtro = " where year(Mov_fecMov) = ". $anio ." and Month(Mov_fecMov) = ". $mes . "  and mov_idprod <> '0' ORDER BY Mov_fecmov desc";

      $consulta= "SELECT mov_familia, mov_idprod,mov_fecmov,mov_cantidad,mov_precio,mov_operacion FROM  MoviProductos  " . $filtro;
      $resbd = DB::connection('bdcomercio')->select($consulta ); 

      foreach ($resbd as $row) {

        // Productos que no computan cantidades
          if (  ($row->mov_idprod == 'par' or $row->mov_idprod =='caja') ) {
              $row->mov_cantidad =0;
          }  
          // Acumulo la Familia
          $id =  trim($row->mov_familia);
          $indice = array_search( $id, array_column($datostabla,'id') ) ;
          if ($indice != '') {
            $this->acumulo_movi($datostabla,$indice,$row);  
          }
          // Acumulo el Producto
          $id =  trim($row->mov_familia) . trim($row->mov_idprod);
          $indice = array_search( $id, array_column($datostabla,'id') ) ;
          if ($indice != '') {
            $this->acumulo_movi($datostabla,$indice,$row);  
          }

      } // end cursor movimientos

    //  unset($datostabla[0]); // NO se puede sino no se ve Borro el elemento auxiliar para busqueda      
      // Actualizo Bd con la salida
      for ($i = 1; $i < count($datostabla); $i++) {
      
          $cierreValor = new CierreMensual_valor;
          $cierreValor->periodo = $periodo;
          $cierreValor->sucursal = env('SUCURSAL_LOCAL');
          $cierreValor->familia = $datostabla[$i] ['familia'];
          $cierreValor->codigo = $datostabla[$i] ['idProd'];
          $cierreValor->ventas = $datostabla[$i] ['ventas'];
          $cierreValor->compras = $datostabla[$i] ['compras'];
          $cierreValor->sucursales = $datostabla[$i] ['sucursales'];
          $cierreValor->ajustes = $datostabla[$i] ['ajustes'];
          $cierreValor->ventas_p = $datostabla[$i] ['ventas_p'];
          $cierreValor->compras_p = $datostabla[$i] ['compras_p'];
          $cierreValor->stock_ini = $datostabla[$i] ['stock_ini'];

          // Calculado
         // $cierreValor->stock =  $cierreValor->stock_ini - $cierreValor->ventas + $cierreValor->compras - $cierreValor->sucursales - $cierreValor->ajustes;
          // El stock que tiene la tabla de productos al momento del proceso
          //  ** es importante que se ejecute al comienzo del mes **  
          $cierreValor->stock = $datostabla[$i] ['stock'];

          $cierreValor->save();
      }
      $mensaje = $mensaje  . 'Se genero Información de Cierre de Mes';

      // Enviar la respuesta Ok.
      $res = [
            "success" => TRUE,
            "mensaje" => $mensaje
      ];

      return response()->json($res);

  } // Fin infcierre_proceso


  private function acumulo_movi(&$datostabla,$indice,$row) {

    if ($row->mov_operacion == 'R' or $row->mov_operacion == 'V') { // Ventas o Anulacion de Ventas
      $datostabla [$indice] ["ventas"] = $datostabla [$indice] ["ventas"] + ( $row->mov_cantidad * -1);
      $datostabla [$indice] ["ventas_p"] = $datostabla [$indice] ["ventas_p"] + $row->mov_precio;
      return;
   
    }elseif ($row->mov_operacion == 'C' ) { // Compras 
      $datostabla [$indice] ["compras"] = $datostabla [$indice] ["compras"] + ( $row->mov_cantidad );
      $datostabla [$indice] ["compras_p"] = $datostabla [$indice] ["compras_p"] + ( $row->mov_precio * -1);
     return;
    }elseif ( $row->mov_operacion == 'Y') { //  Entrada INter Sucursal
      $datostabla [$indice] ["sucursales"] = $datostabla [$indice] ["sucursales"] + ( $row->mov_cantidad );
       return;
    }elseif ( $row->mov_operacion == 'I') { //  Salida INter Sucursal
      $datostabla [$indice] ["sucursales"] = $datostabla [$indice] ["sucursales"] + ( $row->mov_cantidad  * -1 );
       return;
    }elseif ( $row->mov_operacion == 'A' or $row->mov_operacion == 'E' ) { //  Ajustes o Entrada 
      $datostabla [$indice] ["ajustes"] = $datostabla [$indice] ["ajustes"] + ( $row->mov_cantidad );
       return;
    }elseif ( $row->mov_operacion == 'S') { //  Salidas  Manules
      $datostabla [$indice] ["ajustes"] = $datostabla [$indice] ["ajustes"] + ( $row->mov_cantidad );
       return;
    }

  } // Fin acumula


  public function lista()
  {     

      $periodo=$_GET['periodo'];

      $datostabla = [];

      $consulta = "SELECT * FROM cierresmensuales_valores  WHERE periodo = $periodo order by codigo,ventas desc";
      $resbd = DB::select($consulta );
      foreach ($resbd as $row) {
          // Busco datos del producto
          if($row->codigo != ""){
        //      $oproducto  = Producto::find($row->familia,$row->codigo );
        //      $descripcion = $oproducto->Prod_Descripcion;
        //      $reposicion =$oproducto->Prod_StockMin;
          }else{
              $descripcion = '';
              $reposicion =0;
          }

          $stock_resultado =  $row->stock_ini - $row->ventas + $row->compras -$row->sucursales + $row->ajustes;
          $datostabla[] = array(
              'familia'  => $row->familia,  
              'idProd'  => $row->codigo, 
              'descripcion'  => $descripcion, 
              'stock_ini'  => number_format($row->stock_ini,0,"","."),
              'ventas'  =>  number_format($row->ventas,0,"","."),
              'compras'  =>  number_format($row->compras,0,"","."),
              'sucursales'  =>  number_format($row->sucursales,0,"","."),
              'ajustes'  =>  number_format($row->ajustes,0,"","."),
              'ventas_p'  =>  number_format($row->ventas_p,0,"","."),
              'compras_p'  =>  number_format($row->compras_p,0,"","."),
              'stock_resultado'  =>  number_format($stock_resultado,0,"","."),
              'stock'  =>  number_format($row->stock,0,"","."),
 //error  nflia             'stock'  =>  number_format($oproducto->Prod_Stock,0,"","."),
              'reposicion'  => $reposicion 
          );    
      }; // fin foreach 

      $res = [
            "success" => TRUE,
            "results" => $datostabla
      ];
      return response()->json($res);

  } // Fin listo

} // Fin Controlador
