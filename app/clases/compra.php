<?php   namespace app\clases;

use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\producto;  

class compra  {
  		 
  // Propiedades
  // Salida: 
  public $ret = ""; // Retorno de Ejecución  "" = Ok  sino Retoran Mensaje de Error

  // Entrada:

  public static function updateItem ( $valores ) {

    $consulta = "update lotesmovpend set
         mlot_cantidad = :mlot_cantidad
       , mlot_precio = :mlot_precio 
     where(
          mlot_numlot= :mlot_numlot and mlot_fila= :mlot_fila )";

    //Corregir los valores decimales
    $valores['mlot_precio' ] =  numdec($valores['mlot_precio' ] ,2) ;
    $num = DB::insert($consulta,  $valores );

  } 

  public static function addItem ( $valores ) {

    // Busco el nro de fila a insertar
    $consulta = "SELECT max(MLot_Fila) as maxfila FROM lotesmovpend WHERE    MLot_NumLot= ?"  ;
    $datos = DB::select($consulta, [$valores['mlot_numlot'] ] );
    $valores['mlot_fila' ] = $datos[0]->maxfila + 1 ;

    $consulta="insert into lotesmovpend ( mlot_numlot,mlot_sucursal,mlot_fila,mlot_familia,mlot_idprod,mlot_cantidad,mlot_descripcion,mlot_precio) 
     values (
        :mlot_numlot
       ,:mlot_sucursal
       ,:mlot_fila
       ,:mlot_familia
       ,:mlot_idprod
       ,:mlot_cantidad
       ,:mlot_descripcion
       ,:mlot_precio
    )";

    //Corregir los valores decimales
    $valores['mlot_precio' ] =  numdec($valores['mlot_precio' ] ,2) ;
    $num = DB::insert($consulta,  $valores );

  } 

  public static function deleteItem ( $idCompra , $fila ) {

    $consulta = 'DELETE FROM lotesmovpend WHERE  MLot_NumLot  = ? and MLot_Fila = ? ' ;
    $num = DB::delete($consulta, [ $idCompra, $fila ]);

  } 

  public static function leerItems ( $idCompra ) {

      // Todos los Items de la Compra
      $consulta = "SELECT Prod_Descripcion,Prod_Categoria,Prod_Precio,Prod_Precio2,Prod_Costo, MLot_Fila,MLot_Familia,MLot_IdProd,MLot_Cantidad,MLot_Descripcion,MLot_Precio ,MLot_Fila , MLot_Sucursal  , MLot_CantEnv , MLot_Remito
      FROM lotesmovpend  inner join productos on  lotesmovpend.MLot_Familia = productos.Prod_Familia and lotesmovpend.MLot_IdProd = productos.Prod_Id
      WHERE    MLot_NumLot=$idCompra order by MLot_Fila desc";

      $datos = DB::select($consulta);


      return $datos;

  } // Fin Buscar leerItems


  public static function leerItemsRemitoSuc ( $idRemito ) {

      // Todos los Items del Remito  Inter Sucursal 
      $consulta = "SELECT Prod_Descripcion,Prod_Categoria,Prod_Precio,Prod_Precio2,Prod_Costo,Mov_Familia,Mov_IdProd,Mov_Cantidad * -1 as Cantidad , Mov_PrecioUnitario 
      FROM moviproductos  inner join productos on  MoviProductos.Mov_Familia = Productos.Prod_Familia and MoviProductos.Mov_IdProd = Productos.Prod_Id
      WHERE    MOV_IDot=$idRemito AND MOV_operacion ='I' ORDER BY Mov_fecmov desc";

      $datos = DB::select($consulta);

      return $datos;

  } // Fin Buscar leerItems


  public function nuevo(){

    // Completo datos tabla Comprobante , Movimientos y  Pagos

    //DB::beginTransaction();

  	$this->comp_responsable='RAN';
	  $this->comp_fecmov = fechahorahoy();
  	// Recorro todas las lineas de Detalle para calcular los totales
    $totalComp = 0;
    $auxlinea = 0;

    $this->ret =  ""   ; // todo Ok
    return  $this->ret;

                				
  } // Fin Metodo nuevo
	
} // Fin de la clase 
