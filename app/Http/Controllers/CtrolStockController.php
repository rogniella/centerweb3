<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\clases\compra; 
use App\Models\lote;  
use App\Models\producto;  
use App\Models\inventario;  
use App\Models\sucursal;  

class CtrolStockController extends Controller
{

  public function index()
  {
      // Pantalla que Lista Lotes de Ajustes
      return view('ctrol_stock.index'  ) ;
  }

  public function index_partes(Request $request)
  {
      // Pantalla que Lista Partes del Lotes
      // Ya existe , leer los datos de la misma
      if($request->id_lote == 0 ) {
        // Es un Alta, el 1er llamado
        $lote = new lote; 
        $lote->Lot_Operacion = 'T';  // Tipo de Lote Stock
        $lote->Lot_Estado = 'C';    //  En Carga
        $lote->Lot_FecMov = date("Y-m-d"); // Asume fecha del dia
        $lote->Lot_UsuAlta = Auth::user()->name;          
        $lote->save();
      }else{  
        $lote = lote::find($request->id_lote);
      }    
      $lote->Lot_FecMov = date("Y-m-d" , strtotime($lote->Lot_FecMov ) );
       
      //dd($lote);
      $sucursales = sucursal::combo(Auth::user()->sucursal);
      $familias = [ 'REC' => 'REC - Marcos Recetados',
                    'SOL' => 'SOL - Anteojos de Sol' ];
  
      return view('ctrol_stock.index_partes' ,[ 'lote' => $lote,'sucursales' => $sucursales ,'familias' => $familias ]  ) ;

  }

  public function consulta(Request $request)
  {
      // Pantalla que Lista Datos de Lotes ya procesados
      $lote = lote::find($request->id_lote);
       
      //dd($lote);
      $sucursales = sucursal::combo(Auth::user()->sucursal);
      $familias = [ 'REC' => 'REC - Marcos Recetados',
                    'SOL' => 'SOL - Anteojos de Sol' ];
  
      return view('ctrol_stock.consulta' ,[ 'lote' => $lote,'sucursales' => $sucursales ,'familias' => $familias ]  ) ;
  
  }

  public function consulta_datos(Request $request)
  {

    $lote = lote::find($request->numlot);
      
    $datos_total = [];    
    $datos_total[] = [
        'descripcion'  => 'Cantidad en Base Datos',
        'cantidad'  => $lote->Lot_Cant_bd
    ];
    $datos_total[] = [
        'descripcion'  => 'Cantidad Ingresada en Sectores',
        'cantidad'  => $lote->Lot_Cant_ing
    ];
    $datos_total[] = [
        'descripcion'  => 'Cantidad Ajustada',
        'cantidad'  => $lote->Lot_Cantidad
    ];

    // Busco los Prod INGRESADOS, como existentes en todos los sectores
    $datos_ingresados = [];
    // Todos los productos en los lotes de Partes
    $consulta = "SELECT lot_observ, MLot_IdProd,MLot_Cantidad as cantidad FROM lotesmovpend INNER JOIN lotes ON MLot_NumLot = lot_NumLot  WHERE    MLot_NumLot IN (SELECT Lot_Numlot FROM lotes  WHERE    Lot_IdProv= ? )";     
    $item_lotes = DB::select($consulta , [$request->numlot] );
    foreach ($item_lotes as $row) {
      $obs = '';
      $stock_bd = 0;
      if (! $producto  = Producto::findCodigo($lote->Lot_Familia,$row->MLot_IdProd) ) {
          $obs = 'Error: No se encontro en tabla de Productos';
      }    
      if ($inv = inventario::findCodigo($producto->Prod_idWEB,$lote->Lot_Sucursal)  ){
        $stock_bd = $inv->Inv_Stock;
      }
      $datos_ingresados[] = [
        'Lot_Observ' => $row->lot_observ ,
        'Prod_Id'  => $row->MLot_IdProd,
        'Prod_Categoria'  =>  $producto->Prod_Categoria,
        'Prod_Descripcion'  =>  $producto->Prod_Descripcion,
        'marca' => strtok ($producto->Prod_Descripcion , " ") ,
        'Prod_Precio'  => $producto->Prod_Precio,
        'Prod_Precio2'  => $producto->Prod_Precio2,
        'stock_bd'  => $stock_bd,            
        'stock_ingresado'  => $row->cantidad,            
        'obs'  => $obs            
      ];
    }  // For

    // Ajustes realizados
    $datos = [];
    $consulta = "SELECT SUBSTRING_INDEX(Prod_Descripcion, ' ', 1) marca, Prod_Id,Prod_Descripcion,Prod_Precio, mov_cantidad, Mov_idprod,mov_familia from moviproductos INNER JOIN productos ON Mov_Familia = Prod_familia AND Mov_idprod = Prod_id WHERE    mov_idOt= ?  and mov_operacion = 'A'";     
    $datos = DB::select($consulta , [$request->numlot] );


    return response()->json( [  'results' => $datos ,
                                'ingresados' => $datos_ingresados ,
                                'totales' => $datos_total ] );
      
  }


  public function calcular_ajuste(Request $request)
  {

    // Boton calcular Ajuste  (Por ajax)  
    $lote = lote::find($request->numlot);
    $datos = [];
    $cantidad_ing = 0;
    $cantidad_ajuste = 0;
    $cantidad_bd = 0;
    $cantidad_cero = 0;
    $cantidad_otraSuc = 0;
    $datos_total = [];

    if ($lote->Lot_Sucursal == 1 ) {
        $aux_otra_suc = 2;   
    }else{
        $aux_otra_suc = 1;   
    }

    DB::beginTransaction();
 
    // Todos los productos Ingresados en los lotes de Partes
    $consulta = "SELECT MLot_IdProd,sum(MLot_Cantidad) as cantidad FROM lotesmovpend  WHERE    MLot_NumLot IN (SELECT Lot_Numlot FROM lotes  WHERE    Lot_IdProv= ? ) GROUP BY MLot_IdProd";     
    $item_lotes = DB::select($consulta , [$request->numlot] );

    // Para buscar en array , mucho mas rapido que en bd
      $array = (array ) $item_lotes;
    // $array = array ( 1 => $item_lotes);
      $arrayIdProd = array_column( $array , 'MLot_IdProd');

    foreach ($item_lotes as $row) {
        // Busco en produto en bd para comparar Stock
        $obs = '';
        if (! $producto  = Producto::findCodigo($lote->Lot_Familia,$row->MLot_IdProd) ) {
            $obs = 'Error: No se encontro en tabla de Productos';
        }    
        $stock_bd = 0;

        if ($request->filtro == '' OR  strlen(stristr( $producto->Prod_Descripcion,$request->filtro)) > 0      ) {
            $cantidad_ing = $cantidad_ing + $row->cantidad; 
            
            if ($inv = inventario::findCodigo($producto->Prod_idWEB,$lote->Lot_Sucursal)  ){
             $stock_bd = $inv->Inv_Stock;
            } 
            $ajuste =   $row->cantidad - $stock_bd;
            if ($ajuste != 0) {
              $cantidad_ajuste = $cantidad_ajuste + $ajuste; 
              $producto->Prod_UsuUltMan  = "CtrolStockWEB";
              // Inserta reg en tabla de Movimientos de Producto  y Actualiza Stock
              $producto->addMovimiento('A',$ajuste,0, $request->numlot ,0,'','',$lote->Lot_Sucursal);

              //Busco el Stock de la otra suc solo para mostrarlo y poder controlar
              if ($inv = inventario::findCodigo($producto->Prod_idWEB,$aux_otra_suc)  ){
                $stock_bd_otra = $inv->Inv_Stock;
              }

              $datos[] = [
              'Prod_idweb'  => $producto->Prod_idWEB,
              'Prod_Id'  => $row->MLot_IdProd,
              'Prod_Descripcion'  =>  $producto->Prod_Descripcion,
              'Prod_Precio'  => $producto->Prod_Precio,
              'stock_bd'  => $stock_bd,            
              'stock_bd_otra'  => $stock_bd_otra,            
              'stock_ingresado'  => $row->cantidad,            
              'ajuste'  => $ajuste,           
              'obs'  => $obs            
              ];
            } // Si tiene ajuste 
        }else{
            // Es un caso de Error, cargaron producto, pero no del filtro
            if ($request->filtro != '') {
              $datos_total[] = [
                'descripcion'  => $producto->Prod_Descripcion,
                'cantidad'  => 'Err:No Seleccionado'
              ];
            }
        } // If filtro
    }  // For
      
    // Recorro Todos los productos (Segun Filtro) en Bd para validarlos
    $filtro = "";
    if ($request->filtro != '' ) {
        $filtro = "and Prod_Descripcion LIKE '%" .$request->filtro . "%'"; 
    } // If filtro
    $consulta = "SELECT Prod_idWEB, Prod_Id,Prod_Descripcion,Prod_Precio,Prod_Precio2,Inv_Stock FROM productos INNER JOIN inventarios ON Prod_idWEB = Inv_IdProd
        WHERE Prod_Familia = ? and inv_Sucursal = ? and Prod_Id <> '0' and Prod_Estado <> 'I' " . $filtro;     
    $productos = DB::select($consulta , [$lote->Lot_Familia , $lote->Lot_Sucursal ] );
    foreach ($productos as $row) {

        if ($row->Inv_Stock == 0 ) {
            //Valido si no hay stock en todas las sucursales, 
            //  Y si es cero lo Inactivo

            if  ( ! $registro  = producto::find($row->Prod_idWEB) ) {
              displaylog( 'Error: No se encontro el Producto Id:' . $row->Prod_idWEB); 
              continue;
            };
            //displaylog  ( " Estaba en cero: " . $registro->Prod_Id  );
            $cantidad_cero = $cantidad_cero + $this->inactiva_prod_stock_cero($registro,$cantidad_otraSuc);
            continue;
        } 
        // Para los casos que tinen Cantidad en Bd Busco en array de los cargados, mucho mas rapido que en bd
        $obs = '';
        $key = array_search( $row->Prod_Id, $arrayIdProd );

        // Si no lo encuentro, es porque no lo cargo => Hay que corregir a cero en Bd
        if ( !is_numeric( $key) ) {
            $ajuste = $row->Inv_Stock * -1;
            $cantidad_ajuste = $cantidad_ajuste + $ajuste; 
            if (! $producto  = Producto::findCodigo($lote->Lot_Familia,$row->Prod_Id) ) {
                $obs = 'Error: NO ACTUALIZA No se encontro en tabla de Productos';
            } else {
                $producto->Prod_UsuUltMan  = "CtrolStockWeb";
                // Inserta reg en tabla de Movimientos de Producto  y Actualiza Stock
                $producto->addMovimiento('A',$ajuste,0,
                             $request->numlot ,0,'','',$lote->Lot_Sucursal);
                // Y hay que inactivar porque queda en cero
               // displaylog  ( " Lo dejo en cero: " . $producto->Prod_Id  );
                $cantidad_cero = $cantidad_cero + $this->inactiva_prod_stock_cero($producto,$cantidad_otraSuc);
            } //fin if producto
            $datos[] = [
              'Prod_idweb'  => $row->Prod_idWEB,
              'Prod_Id'  => $row->Prod_Id,
              'Prod_Descripcion'  => $row->Prod_Descripcion,
              'Prod_Precio'  => $row->Prod_Precio,
              'stock_bd'  => $row->Inv_Stock,            
              'stock_ingresado'  => 0,            
              'ajuste'  => $ajuste ,
              'obs'  => $obs            
            ];
        } // fin if no encotro en los ingresados   
    } // fin de for 

    $cantidad_bd = $cantidad_ing - $cantidad_ajuste;
    
    $datos_total[] = [
        'descripcion'  => 'Cantidad en Base Datos (Sin Corregir)',
        'cantidad'  => $cantidad_bd
    ];
    $datos_total[] = [
        'descripcion'  => 'Cantidad Ingresada como Correcto',
        'cantidad'  => $cantidad_ing
    ];
    $datos_total[] = [
        'descripcion'  => 'Cantidad Ajustada',
        'cantidad'  => $cantidad_ajuste
    ];
    $datos_total[] = [
      'descripcion'  => 'Cantidad Sin Stock => Inactivos',
      'cantidad'  => $cantidad_cero
    ];
    $datos_total[] = [
      'descripcion'  => 'Cantidad Prod en Otras Suc => Activos',
      'cantidad'  => $cantidad_otraSuc
    ];
   
    if (  $request->actualiza == 'SI' ) {
        // Cierro Lote
        $lote->Lot_Cant_bd = $cantidad_bd;
        $lote->Lot_Cant_ing = $cantidad_ing;
        $lote->Lot_Cantidad = $cantidad_ajuste;
        $lote->Lot_Estado = 'F'; // Paso a Estado finalizado
        $lote->Lot_FecMov = fechahorahoy ();  // Queda la fecha que se procesa
        $lote->save();

        // Cierro Lotes de las Partes
        $consulta ="UPDATE lotes SET Lot_Estado = 'F' WHERE Lot_IdProv= ?";
        $item = DB::update($consulta , [$request->numlot] );
        DB::commit();
        $datos = []; // Como es definitiva lo limpia

    }else{
        DB::rollBack(); 
        // Actualiza solo los valores del lote
        $lote->Lot_Cant_bd = $cantidad_bd;
        $lote->Lot_Cant_ing = $cantidad_ing;
        $lote->Lot_Cantidad = $cantidad_ajuste;
        $lote->Lot_FecMov = fechahorahoy ();  // Queda la fecha que se procesa
        $lote->save();
    }  

    return response()->json( [  'results' => $datos ,
                                'totales' => $datos_total ] );
      
  }

  private function inactiva_prod_stock_cero($prod, &$cant_otraSuc)
  {
      // Valida si en todas las suc esta sin stock
      // Si corresponde lo inactiva y retorna 1 , sino 0
    //  $consulta = "SELECT sum(Inv_Stock)  as cantidad FROM inventarios  WHERE Inv_IdProd=? ";
      $consulta = "SELECT Inv_Stock  as cantidad FROM inventarios  WHERE Inv_IdProd=? and Inv_Stock <> 0 ";
      $dat = DB::select($consulta, [$prod->Prod_idWEB ] );
      //if ($dat[0]->cantidad != 0 ) {
      if ($dat ) {
        //displaylog( 'Tiene en otra Suc,No Inactivo el Producto Id:' . $prod->Prod_idWEB  .' '. $prod->Prod_Descripcion ); 
        $cant_otraSuc = $cant_otraSuc + 1;
        return 0;
      }else{
        $prod->Prod_Estado = 'I'; //Inactivo
        $prod->Prod_UsuUltMan = 'CtrolStockWEBi';
        $prod->save();            
        return 1;
      }



  }


  public function buscar(Request $request)
  {
      // Carga Pantalla de Lotes , carga la tabla
      $datos = lote::buscarCtrolStock($request->tipo_consulta, $request->tipo_lote, $request->numlot, Auth::user()->sucursal );
      return response()->json( ['results' => $datos] );
  }

  public function create(Request $request)
  {
    // Carga pantalla de un Sector (Nueva o Continuar cargando alguna)
    if($request->id_lote == 0 ) {
      // Es un Alta
      $lote = lote::find($request->num_lote); //Lote totalizador
      $loteCompra = new lote; 
      $loteCompra->Lot_Numlot = 0;
      $loteCompra->Lot_FecMov = date("Y-m-d"); // Asume fecha del dia
      $loteCompra->Lot_Operacion  = "S"; // S = Control Sotock Sector
      $loteCompra->Lot_Estado  = 'C'; // En proceso de Carga
      $loteCompra->Lot_IdProv  = $lote->Lot_Numlot;  // Lote totalizador
      $loteCompra->Lot_Sucursal  = $lote->Lot_Sucursal ; //Tomo valores del totalizador
      $loteCompra->Lot_Familia  = $lote->Lot_Familia ; //Tomo valores del totalizador
      $lote->Lot_UsuAlta = Auth::user()->name;     
      $loteCompra->save();
    }else{
      // Ya existe , leer los datos de la misma
      $loteCompra = lote::find($request->id_lote);
    }

   // $loteCompra->Lot_FecMov = date("Y-m-d" , strtotime($loteCompra->Lot_FecMov ) );
    $loteCompra->Lot_Proveedor = ''; 
      
    $sucursales = sucursal::combo(Auth::user()->sucursal);

    return view('ctrol_stock.create' , [ 'lote' => $loteCompra,'sucursales' => $sucursales ] );
  
  }


  public function ActualizaDatosLote(Request $request)
  {
      // Actualiza los datos de Cabecera  cada vez que cambia alguno
      $loteCompra = lote::find($request->idcompra);
      $loteCompra->Lot_Observ =  $request->observ;
      if ( $request->familia != '' ) {
        if ( $request->familia == 'BAJA' ) {
          $loteCompra->Lot_Estado  = 'I'; // Inactivo - Baja
        }else{  
          $loteCompra->Lot_Sucursal  =  $request->sucursal ;
          $loteCompra->Lot_Familia =  $request->familia;
          $loteCompra->Lot_Filtro =  $request->filtro;
          $loteCompra->Lot_FecMov = $request->fecmov;
        }    
      } 

      $loteCompra->save() ;

      // Retorna el Id
      return response()->json( [ 'idcompra' => $loteCompra->Lot_Numlot  ] );
  }

  public function CargaItems(Request $request)
  {
      // Completar datos de 1 compra
      $datos = compra::leerItems($request->idcompra);
      return response()->json( ['results' => $datos] );
  }

  public function DeleteItem(Request $request)
  {
      // Elimina 1 Item de 1 compra
      compra::deleteItem($request->idcompra,$request->fila);
      return response()->json( ['results' => 'Ok'] );
  }

  public function AddItem(Request $request)
  {
     if ($request->idcompra == 0 ) {
        $ret= 'Error: No Ingreso Nro de Lote';
        return response()->json( [ 'error' => $ret ] );              
     }


      // Inserta 1 Item de la compra
      $valores = [
        'mlot_numlot'  => $request->idcompra 
       ,'mlot_sucursal'  =>  0
       ,'mlot_fila'  => $request->fila
       ,'mlot_familia'  => $request->familia
       ,'mlot_idprod'  => $request->idprod
       ,'mlot_cantidad'  => $request->cantidad
       ,'mlot_precio'  => 0
       ,'mlot_descripcion'  => ""
      ];

      compra::addItem($valores);

      // Alta del Producto  o Actualizo los precios de ser necesario el producto 
      if (! $oproducto = Producto::findCodigo($valores['mlot_familia'], $valores['mlot_idprod'] )) { 
        $ret= 'Error: No Encontro Producto' . $valores['mlot_idprod'];
        return response()->json( [ 'error' => $ret ] );              
      }  
 
      // Por si modifico precio
      $oproducto->Prod_Precio = $request->precio;
      $oproducto->Prod_Precio2 = $request->precio_min;
      $oproducto->Prod_UsuUltMan = 'CtlStockWEB';
      $oproducto->Prod_Estado = ''; // Re Activo por las dudas
      $oproducto->actualizar() ;        
 
      // Retorna el IdCompra por si se genero
      return response()->json( [ 'idcompra' => $request->idcompra  ] );

  }  // Fin AddItem

  public function UpdateItem(Request $request) {

      // Actualiza 1 Item de la compra
      $valores = [
        'mlot_numlot'  => $request->idcompra 
       ,'mlot_fila'  => $request->fila
       ,'mlot_cantidad'  => $request->cantidad
       ,'mlot_precio'  => $request->precio_lista
      ];

      compra::updateItem($valores );

      // Actualizo de ser necesario el producto 
      $oproducto  = Producto::findCodigo($request->familia, $request->idprod ) ; 
      $oproducto->Prod_Descripcion  = $request->descripcion;
      $oproducto->Prod_Categoria  = $request->categoria;
      $oproducto->Prod_Costo = $request->costo;
      $oproducto->Prod_Precio = $request->precio;
      $oproducto->Prod_Precio2 = $request->precio_min;
      $oproducto->Prod_UsuUltMan = 'CtlStockWEB';
      $oproducto->actualizar() ;        

      return response()->json( ['results' => 'ok' ] );
  }

} // Fin de la Clase