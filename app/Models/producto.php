<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)
use App\Models\inventario;  
use App\Models\hisproducto;  

class producto extends Model {

	protected $table      =  "productos";
	protected $primaryKey =  'Prod_idWEB';
  public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"

	protected $fillable = ['Prod_Familia','Prod_Id', 'Prod_Categoria', 'Prod_Descripcion','Prod_Precio','Prod_Precio2','Prod_Costo','Prod_Estado','Prod_Marca']; 

  //  lista de campos tabla moviproductos
  private $mov_fecmov='';
  private $mov_familia='';
  private $mov_idprod='';
  private $mov_cantidad=0;
  private $mov_precio=0;
  private $mov_stock=0;
  private $mov_operacion='';
  private $mov_idot=0;
  private $mov_idprov=0;
  private $mov_motivo='';
  private $mov_responsable='';
  private $mov_usualta='';
  private $mov_fecalta='';
  private $mov_descripcion='';
  private $mov_preciounitario=0;
  private $mov_cantvendida=0;
  private $mov_costocompra=0;
  private $mov_tipoot='';  
  public $mov_sucursal= 0 ; 
      // LO hice public porque lo cambio de afuera
  private $mov_sucursaldestino=0;
  private $mov_idweb=0; 
  private $mov_bonif= 0 ;  

  public $indicadorModifico = false;

  public function save(array $options = array()) {

      // Si la instancia ya está en base de datos, es un Update, sino es un Insert

      if ($this->exists) {
          //Update ***  PARA PRODUCTOS no funciona
          $campos_modificados = $this->getDirty();
          if (count($campos_modificados) == 0 ) {
             return false;
          }
          foreach($campos_modificados as $campo=>$valor) {

              If (trim($valor) != trim($this->getOriginal( $campo) )  and 
                  $campo != 'Prod_UsuUltMan'  ) {
                    displaylog  ( $this->Prod_Familia . $this->Prod_Id . " " . $campo . " Nvo.Valor: " . $valor  );
                    displaylog  ( '  Ant.Valor:' .    $this->getOriginal( $campo) );
      
                    $this->indicadorModifico = true;
                    $this->insertHistoria( $campo  , $this->getOriginal( $campo ) , $valor );
                    $this->Prod_FecUltMan = fechahorahoy(); 
              }    
          }        

      }else{
          // Insert 
          $this->Prod_FecAlta = fechahorahoy();     
          $this->Prod_FecUltMan = fechahorahoy(); 
          if($this->Prod_CodBarra == '') { 
              $this->Prod_CodBarra = $this->Prod_Familia . $this->Prod_Id;
          }  
          if($this->Prod_TasaIva == 0) { 
              $this->Prod_TasaIva = 1; // Valor por defecto
          }  
          $this->Prod_Estado = ''; // Valor por defecto

          $this->insertHistoria( "Alta" ,'' , '');
      }   
  
      return parent::save($options );
      
  } // Fin Save

  public static function findCodigo($familia, $id ){

      // Busca por Id , como tiene clave compuesta tengo que redefinir

      $datos = Producto::where('Prod_Familia', '=', $familia)->where('Prod_Id', '=', $id)
            ->first();
        
      return $datos;

  } // Fin find

  public static function findCategoria($familia, $categoria ){

      // Busca por Categoria , como tiene clave compuesta tengo que redefinir

      $datos = Producto::where('Prod_Familia', '=', $familia)->where('Prod_Categoria', '=', $categoria)
            ->first();
        
      return $datos;

  } // Fin find

  public static function reemplaza_codigo($familia, $id ,  $id_nuevo ){

    // Cambia el Codigo del Producto para poder reutilizar

    $prod_nvo = Producto::findCodigo($familia,$id_nuevo );
    if ( ! $prod_nvo){
      displaylog( 'Error al buscar Producto: ' . $familia . $id_nuevo );
    }    
    displaylog( 'Falló la Generación del PDF: ' );

    $prod_nvo->Prod_UsuUltMan = "JuntaCod";
    displaylog( 'Falló la Generación del PDF: ' . $prod_nvo->Prod_UsuUltMan );

    
    $prod_nvo->insertHistoria( "JuntaCod" , $id , $id_nuevo);

    if ( ! $prod_nvo ) {
       return "No existe el Código Destino : " . $id_nuevo;
    }
    // Reasigno todas las relaciones, antes de modificar
    if ( $familia == "REC" ) {
      $query_operacion = "UPDATE ot_ant SET  otant_lejarmazon = '{$id_nuevo}' WHERE otant_lejfamarmazon = '$familia' and   otant_lejarmazon= '{$id}'";
      $resultado = DB::update($query_operacion); 
      displaylog ($familia . " " . $id . " Arm Lejos:" . $resultado);

      $query_operacion = "UPDATE ot_ant SET  otant_cerarmazon = '{$id_nuevo}' WHERE    otant_cerarmazon= '{$id}'";
      $resultado = DB::update($query_operacion); 
      displaylog ($familia . " " . $id . " Arm Cerca:" . $resultado);
    }
    $query_operacion = "UPDATE moviproductos SET  mov_idprod = '{$id_nuevo}' WHERE mov_familia = '$familia' and   mov_idprod= '{$id}'";
    $resultado = DB::update($query_operacion); 
    displaylog ($familia . " " . $id . " Mov:" . $resultado);

    $query_operacion = "DELETE FROM hisproductos WHERE hisprod_familia = '$familia' and   hisprod_idprod= '{$id}'";
    $resultado = DB::update($query_operacion); 
   // displaylog ($familia . " " . $id . " Hisprod:" . $resultado);

    $query_operacion = "DELETE FROM productos   WHERE prod_familia = '$familia' and   prod_id= '{$id}'";
    $resultado = DB::update($query_operacion); 
    displaylog ($familia . " " . $id . " Prod:" . $resultado);

 } // Fin Reemplaza_Codigo

  public static function cambia_codigo($familia, $id ,  $id_nuevo ){

        // Cambia el Codigo del Producto para poder reutilizar

        // Reasigno todas las relaciones, antes de modificar
        $query_operacion = "UPDATE ot_ant SET  otant_lejarmazon = '{$id_nuevo}' WHERE otant_lejfamarmazon = '$familia' and   otant_lejarmazon= '{$id}'";
        $resultado = DB::update($query_operacion); 
        displaylog ($familia . " " . $id . " Arm Lejos:" . $resultado);

        $query_operacion = "UPDATE ot_ant SET  otant_cerarmazon = '{$id_nuevo}' WHERE    otant_cerarmazon= '{$id}'";
        $resultado = DB::update($query_operacion); 
        displaylog ($familia . " " . $id . " Arm Cerca:" . $resultado);

        $query_operacion = "UPDATE moviproductos SET  mov_idprod = '{$id_nuevo}' WHERE mov_familia = '$familia' and   mov_idprod= '{$id}'";
        $resultado = DB::update($query_operacion); 
        displaylog ($familia . " " . $id . " Mov:" . $resultado);

        $query_operacion = "UPDATE hisproductos SET  hisprod_idprod = '{$id_nuevo}' WHERE hisprod_familia = '$familia' and   hisprod_idprod= '{$id}'";
        $resultado = DB::update($query_operacion); 
        displaylog ($familia . " " . $id . " Hisprod:" . $resultado);

        //Le cambia el cod y lo dejo Inactivo
    //TEMPO    $query_operacion = "UPDATE productos SET  prod_id = '{$id_nuevo}' ,  prod_codbarra = '$familia{$id_nuevo}', prod_estado = 'I',Prod_UsuUltMan = 'WebCod'  WHERE prod_familia = '$familia' and   prod_id= '{$id}'";
        $query_operacion = "UPDATE productos SET  prod_id = '{$id_nuevo}' ,  prod_codbarra = '$familia{$id_nuevo}', prod_estado = 'A',Prod_UsuUltMan = 'WebCod'  WHERE prod_familia = '$familia' and   prod_id= '{$id}'";
        $resultado = DB::update($query_operacion); 
        displaylog ($familia . " " . $id . " Prod:" . $resultado);

        $prod_nvo = Producto::findCodigo($familia,$id_nuevo );

  //tempo      $prod_nvo->insertHistoria( "CambioCod" , $id , $id_nuevo);

  } // Fin Cambia_Codigo

  public static function generarNvoCodigo( $familia ){

    // Dependiendo de la Famila Genero Nvo Codigo Libre
    $consulta = "SELECT Flia_MaxId FROM familias  WHERE    Flia_Id= ?";
    $datos = DB::select($consulta, [$familia] );

    $codigo = "";
    $naux = $datos[0]->Flia_MaxId;
    while ( $naux < 85000 ) {
      $naux = $naux + 1; 
      if($naux <= 10000) {
        $codigo = sprintf("%'.04d", $naux);   
      }else{  
        $codigo = sprintf("%'.05d", $naux);   
      }
      $consulta = "SELECT Prod_Id FROM productos  WHERE   Prod_Familia= ? AND Prod_Id= ?" ;
      $datos = DB::select($consulta, [$familia,$codigo] );
      if (!$datos){
         break;  // NO encontro en Bd el codigo Sale del while , para tomarlo como el nuevo       
      }
    } // Fin While   

    return $codigo;

  } // Fin generarNvoCodigo


  public static function actualizaNvoCodigo( $familia,$codigo ){

    // Actualiza el Max codigo
    $consulta = "UPDATE familias SET Flia_MaxId = ?   WHERE    Flia_Id= ?";
    $datos = DB::update($consulta, [ $codigo , $familia] );
    return;

  } // Fin ActualizaNvoCodigo

  public static function listar_movimientos( $familia, $idProd ,$limite = 100 ){
      
      $consulta= "SELECT * ,  CONCAT(mov_motivo,' ',mov_descripcion) as descripcion FROM moviproductos Where mov_familia = ? and mov_idProd = ? ORDER BY mov_idWEB DESC LIMIT $limite";

      $ret = DB::select($consulta, [ $familia, $idProd ]);

    //  dd('holla',$ret,$familia, $idProd);
      return $ret;                   

  }

  public static function buscar_movimientos( $filtro_fecini,$filtro_fecfin, $filtro_sucursal ='',
        $filtro_tipo_operacion ='', $filtro_familia ='',$filtro_idprod ='' ,$filtro_descripcion ='', $filtro_cero= 'N' , $limite = 1000){
        // Listado de movimientos del producto, dependiendo de los filtros
        // Tambien lo utiliza el auto completar
        // Concatenar según la consulta. Armo scrip de consulta
        $filter = " where 1=1";
        $valores = [];

        if ($filtro_fecini != "") {
            $filtro_fecini = $filtro_fecini . " 00:00:00";
            $filtro_fecfin = $filtro_fecfin . " 23:59:59";
            $filter .= " AND Mov_FecMov >= ? and Mov_FecMov <= ?";
            $valores[] = $filtro_fecini;
            $valores[] = $filtro_fecfin;
        }

        if ($filtro_familia != "") {
            $filter .= " AND mov_familia = ?";
            $valores[] =  $filtro_familia ;            
        }

        if ($filtro_tipo_operacion != "") {
            $valores[] =  $filtro_tipo_operacion ;
            if ($filtro_tipo_operacion == "V") { // agregamos las anuaciones
                $filter .= " AND ( mov_operacion = ? or Mov_operacion = 'R' )";
            }else{
                $filter .= " AND mov_operacion = ?";              
            }    
        }

        if ($filtro_cero == "S") {
            $filter .= "  and mov_idprod <> '0'";              
        }

        if ($filtro_sucursal != "0") {
            $filter .= " AND mov_sucursal = ?";
            $valores[] =  $filtro_sucursal ;
        }

        if ($filtro_idprod != '' ) {
            $filter .= " AND mov_idprod = ?";
            $valores[] =  $filtro_idprod ;
        }

        if ($filtro_descripcion != "") {
            $filter .= " AND  ( prod_id  LIKE ? OR prod_descripcion  LIKE ? ) ";
            $valores[] = '%' . $filtro_descripcion . '%';
            $valores[] = '%' . $filtro_descripcion . '%';
        }
        
        $filter .=  " order by mov_idWEB ";

        $consulta= "SELECT  * , prod_descripcion ,UPPER(SUBSTRING_INDEX(Prod_Descripcion, ' ', 1)) marca FROM  moviproductos INNER JOIN productos 
            ON mov_familia = prod_familia AND mov_idprod = prod_id " . $filter . " LIMIT $limite";
 
        $ret = DB::select($consulta,$valores);

        return $ret;                   

  } // Fin Listar


  public static function listar_auditoria( $familia, $idProd ,$limite = 100 ){
      
      $consulta= "SELECT * FROM hisproductos Where HisProd_familia = ? and HisProd_idProd = ? ORDER BY HisProd_idWEB DESC LIMIT $limite";
      $ret = DB::select($consulta, [ $familia, $idProd ]);
      return $ret;                   

  }

  public static function listar( $filtro_familia ='',$filtro_descripcion ='',$limite = 1000,$filtro_monto ='' ,$filtro_ultact ='',$filtro_estado ='',$filtro_marca =''  ){
        // Listado principal, dependiendo de los filtros
        // Tambien lo utiliza el auto completar
        // Concatenar según la consulta. Armo scrip de consulta
        $filter = " where 1=1";
        $valores = [];
        if ($filtro_familia != "") {
            $filter .= " AND prod_familia = ?";
            $valores[] =  $filtro_familia ;
        }

        if ($filtro_descripcion != "") {
            $filter .= " AND  ( prod_id  LIKE ? OR prod_descripcion  LIKE ? ) ";
            $valores[] = '%' . $filtro_descripcion . '%';
            $valores[] = '%' . $filtro_descripcion . '%';
        }

        if ($filtro_monto != "") {
            $filter .= " AND  prod_precio  > ?  ";
            $valores[] = $filtro_monto;
        }
        if ($filtro_ultact != "") {
    //        $filter .= " AND   prod_fecultman  > '2017-01-01 12:00' ";
            $filter .= " AND   Prod_FecAlta  > '2017-01-01 12:00' ";
            $filter .= " AND   Prod_Precio  > 2000 ";
            $filter .= " AND   prod_fecultman  < ? and Prod_FecAlta  < ?   ";
            $valores[] = $filtro_ultact;
            $valores[] = $filtro_ultact;
        }  
        switch ($filtro_estado) {
          case "T":
            // Si marco con T (es porque quiere todos los regitros)
            break;
          case 'I': // Solo los Inactivos
            $filter .= " and prod_estado = 'I'  ";
            break;
          default: // Solo los Activos
            $filter .= " and prod_estado <> 'I'  ";
        }
        if ($filtro_marca != "") {
            $filter .= " AND  prod_marca  = ?  ";
            $valores[] = $filtro_marca;
        }
        
        $filter .=  " order by prod_descripcion ";

        $consulta= "SELECT  prod_idweb, prod_familia, prod_id,prod_descripcion,prod_categoria,prod_costo,prod_precio,prod_precio2
          , (select Inv_Stock from inventarios where Inv_IdProd = prod_idweb and Inv_Sucursal = 1) as stock01
          , (select Inv_Stock from inventarios where Inv_IdProd = prod_idweb and Inv_Sucursal = 2) as stock02
          , prod_fecultman , prod_usuultman ,  prod_usualta , prod_marca , UPPER(SUBSTRING_INDEX(Prod_Descripcion, ' ', 1)) marca FROM productos " . $filter . " LIMIT $limite";

/*  ** es mas rapido poniendo en el select
$consulta= "SELECT  prod_idweb, prod_familia, prod_id,prod_descripcion,prod_categoria,prod_costo,prod_precio,prod_precio2
,0 as stock01 
,0 as stock02 
, prod_fecultman , prod_usuultman ,  prod_usualta , prod_marca  FROM productos " . $filter . " LIMIT $limite";
*/
        $ret = DB::select($consulta,$valores);

        return $ret;                   

  } // Fin Listar

  public function addMovimiento($operacion,$cantidad,$precioUnitario,  $idOperacion,$idProv = 0
               , $detalle = '' , $tipoOt ='',  $sucursal =0 , $bonif =0  ) {

        if ($operacion ==  'V_OnLine') {
          $indicadorVtaOnline = 'S';
          $operacion = 'V';
        }else{
          $indicadorVtaOnline = 'N';
        }

        $this->mov_fecmov = fechahorahoy();
        $this->mov_familia = $this->Prod_Familia;
        $this->mov_idprod = $this->Prod_Id;
        $precio = $precioUnitario *  $cantidad * ( 1 - ($bonif / 100) ) ;
        switch ($operacion) {
            case "A": //Ajuste 
              $this->mov_cantidad = $cantidad ; 
              $this->mov_precio = $precio;  // El precio en +
              $this->mov_motivo = "Lote Ajus:" . numdec($idOperacion);
              $this->mov_stock = 0; //   falta calcular
              break;            
            case "V": //Venta 
              $this->mov_cantidad = $cantidad *  -1; // Venta va en negativo
              $this->mov_precio = $precio;  // El precio en +
              $this->mov_motivo = "Web Vta.Dir N°:" . numdec($idOperacion);
              $this->mov_stock = 0; //   falta calcular
              break;
            case "P": //Presupuesto 
                $this->mov_cantidad = $cantidad *  -1; // Venta va en negativo
                $this->mov_precio = $precio;  // El precio en +
                $this->mov_motivo = "Presupuesto N°:" . numdec($idOperacion);
                $this->mov_stock = 0; 
                break;
            case "I": //Salida a Sucursal 
              $this->mov_cantidad = $cantidad *  -1; // Venta va en negativo
              $this->mov_precio = $precio;  // El precio en +
              $this->mov_motivo = "Remito Sal:" . numdec($idOperacion);
              $detalle = "Envio a Suc:" . numdec($idProv);
              $this->mov_stock = 0; //   falta calcular
              break;
            case "Y": // Ingreso de productos de Otra Sucursal
              $this->mov_cantidad = $cantidad ; // como Compra va +
              $this->mov_stock = $cantidad;
              $this->mov_motivo = "Remito Ent:" . numdec($idOperacion);
              $this->mov_precio = $precio * -1 ; // Compra el precio va -
              $detalle = "Recibio de Suc:" . numdec($idProv);
              break;
            default:
              $this->mov_cantidad = $cantidad ; // Compra va +
              $this->mov_stock = $cantidad;
              $this->mov_precio = $precio * -1 ; // Compra el precio va -
        }

        $this->mov_operacion = $operacion; 
        $this->mov_idot = $idOperacion;  // Va el lote de Compra/Ajuste o Id Ot o Id Vta
        $this->mov_idprov = $idProv; // En las compras
        $this->mov_tipoot =$tipoOt;
        $this->mov_responsable=$this->Prod_UsuUltMan;
        $this->mov_usualta =$this->Prod_UsuUltMan;
        $this->mov_fecalta =fechahorahoy();
        $this->mov_preciounitario = $precioUnitario;
        //   falta ** Se completa sole en las Ventas Mov_operacion = V **
        //En los casos de venta, se busca con que costo se adquirio la mercaderia
        $this->mov_costocompra =0; 
 

        $this->mov_descripcion = $detalle ;  
        $this->mov_sucursaldestino = $sucursal ;
        if ($indicadorVtaOnline == 'S') {
          $this->mov_sucursal = 99;  // Indica venta online
        }else{
          $this->mov_sucursal = $sucursal; //Donde se genera el Movimento            
        }    

//        displaylog ("Sucursal:" . $this->mov_sucursal  . " Env:" . env('SUCURSAL_LOCAL') );
        if ($this->mov_sucursal == '') {
          $this->mov_sucursal = 0;
        }
        $this->mov_bonif = $bonif;       

        $ret = $this->insert_moviproductos();
        if ($ret <> '') {
           $ret =  "Err Item 1: " . $ret  ;
           return  $ret;
        }   

        if ($operacion == "P") {  // Si es Presupuesto no Actualizo Stock
            return  ""; // Ok Si llego hasta aqui
        }
        // Actualiza el Stock 
        if (! $inventario = Inventario::findCodigo( $this->Prod_idWEB ,$sucursal)) { 
          $inventario  = new Inventario;
          $inventario->Inv_idProd = $this->Prod_idWEB;
          $inventario->Inv_Sucursal = $sucursal;
          $inventario->Inv_Stock = 0;
        }  

        $inventario->Inv_Stock = $inventario->Inv_Stock +  $this->mov_cantidad;

//        dd( $inventario);
        if ( ! $inventario->save() ) {
            $ret =  " Error al actualizar stock en tabla Inventario " ;
            return  $ret;
        };  

        return  ""; // Ok Si llego hasta aqui


  } // Fin addMovimiento

  public function actualizar(array $options = array()) {

   		$this->indicadorModifico = false; // Auxiliar para Marcar si actualiza algo


    	/* NO ENCONTRE LA FORMA DE CHEQUEAR AUTOMATICAMENTE TODOS LOS CAMPOS , PARA NO PREGUNTAR UNO POR UNO
    	$campos_modificados = $this->getDirty();
    	if (count($campos_modificados) == 0 ) {
    		return false;
    	}

  		foreach($campos_modificados as $campo=>$valor) {
		        displaylog	( $campo . " valor: " . $valor  );
		   //     displaylog	( 		$this->getOriginal(	'$campo') );
		   //     displaylog	( 		$this->getOriginal(	$campo) );
		  // //     displaylog	( 		$this->getOriginal(	'{$campo}') );
		}
		*/
        If ($this->getOriginal(  'Prod_Descripcion') !=  $this->Prod_Descripcion ) {
            $this->indicadorModifico = true;
            $this->insertHistoria( "Descripcion" , $this->getOriginal( 'Prod_Descripcion') , $this->Prod_Descripcion);
            $this->Prod_FecUltMan = fechahorahoy();
        }        
        If ($this->getOriginal(  'Prod_Categoria') !=  $this->Prod_Categoria ) {
            $this->indicadorModifico = true;
            $this->insertHistoria( "Categoria" , $this->getOriginal('Prod_Categoria') , $this->Prod_Categoria);
            $this->Prod_FecUltMan = fechahorahoy();
        }        
        If ( numdec($this->getOriginal(	'Prod_Precio'),0) !=  numdec($this->Prod_Precio,0) ) {
	   		    $this->indicadorModifico = true;
            $this->insertHistoria( "Precio" , numdec($this->getOriginal(  'Prod_Precio'),0) , numdec($this->Prod_Precio),0);
            $this->Prod_FecUltMan = fechahorahoy();
        }        
        /* Ya no lo audito  11/2021
        If ( numdec($this->getOriginal(	'Prod_Precio2'),0) !=  numdec($this->Prod_Precio2, 0) ) {
	   		    $this->indicadorModifico = true;
            $this->insertHistoria( "Precio2" , numdec($this->getOriginal( 'Prod_Precio2'),0) , numdec($this->Prod_Precio2,0) );
        }        
        */
        If ( numdec($this->getOriginal(	'Prod_Costo'),0) !=  numdec($this->Prod_Costo,0) ) {
	   		   // dd($this->getOriginal(  'Prod_Costo'),$this->Prod_Costo);
            $this->indicadorModifico = true;
            $this->insertHistoria( "Costo" , numdec($this->getOriginal( 'Prod_Costo'),0) , numdec($this->Prod_Costo,0) );
            $this->Prod_FecUltMan = fechahorahoy();
        }     


        return parent::save($options );

/*
        $this->prod_fecultman = fechahorahoy();

        if ($ret == true) {

          $valores = [];

          $valores[] =  numdec($this->Prod_Precio, 2) ;

          $valores[] = $this->Prod_Descripcion  ;   


          $valores[] =  $this->Prod_Categoria ;
           //      dd(1,$valores,$this->Prod_Categoria );    
          $valores[] =  numdec($this->Prod_Precio2, 2) ;
        

          $valores[] =  numdec($this->Prod_Costo, 2) ;
 
          $valores[] =  $this->Prod_UsuUltMan ;
          $valores[] =  $this->prod_fecultman ;
          $valores[] =  $this->Prod_Familia ;
          $valores[] =  $this->Prod_Id ;

          $query_operacion = "UPDATE productos SET  
                       Prod_Precio = ?  
                  ,Prod_Descripcion = ?  
                  ,Prod_Categoria = ? 
	                ,Prod_Precio2 = ? 
	                ,Prod_Costo = ?  
	                , Prod_UsuUltMan = ? 
	                , Prod_FecUltMan = ?
			   WHERE ( Prod_Familia = ? AND Prod_Id = ?);";
 
 		    $datos = DB::update($query_operacion,$valores);        	
        
        }

        return $ret;
*/

  } // Fin Actualizar
   	

	public function insertHistoria( $campo,$valorant,$valornvo) {
    
      // AUDITAR CAMBIOS, Completar los campos antes de llamar 
	    //displaylog	('Cambio :  ' . $campo . '  Valor Ant:' . $valorant .' ValorActual:' . $valornvo );	
        $hisprod_familia= $this->Prod_Familia;
        $hisprod_idprod=$this->Prod_Id ;
        $hisprod_campo=$campo;
        $hisprod_valorant=$valorant;
        $hisprod_valornvo=$valornvo;
        $hisprod_usuario= $this->Prod_UsuUltMan;
        $hisprod_fecha=fechahorahoy();
        $hisprod_sucursalorig=  0;  //  env('SUCURSAL_LOCAL');

        $query_operacion="insert into hisproductos ( hisprod_familia,hisprod_idprod,hisprod_campo,hisprod_valorant,hisprod_valornvo,hisprod_usuario,hisprod_fecha,hisprod_sucursalorig) 
         values (
        '$hisprod_familia'
       ,'$hisprod_idprod'
       ,'$hisprod_campo'
       ,'$hisprod_valorant'
       ,'$hisprod_valornvo'
       ,'$hisprod_usuario'
       ,'$hisprod_fecha'
       ,'$hisprod_sucursalorig'
       )";

  //     dd(env('SUCURSAL_ENVIO_MAIL'),$hisprod_sucursalorig,$query_operacion);
	    $datos = DB::insert($query_operacion);
  
	} // Fin insertHistoria

  private function insert_moviproductos(){
            
      try {
            $query_operacion="insert into moviproductos ( mov_id, mov_fecmov,mov_familia,mov_idprod,mov_cantidad,mov_precio,mov_stock,mov_operacion,mov_idot,mov_idprov,mov_motivo,mov_responsable,mov_usualta,mov_fecalta,mov_descripcion,mov_preciounitario,mov_cantvendida,mov_costocompra,mov_tipoot,mov_sucursal,mov_sucursaldestino,mov_idweb,mov_bonif) 
             values (
               0
               ,'$this->mov_fecmov'
               ,'$this->mov_familia'
               ,'$this->mov_idprod'
               ,'" . numdec($this->mov_cantidad, 0)."'
               ,'" . numdec($this->mov_precio, 2)."'
               ,'" . numdec($this->mov_stock, 0)."'
               ,'$this->mov_operacion'
               ,'$this->mov_idot'
               ,'$this->mov_idprov'
               ,'$this->mov_motivo'
               ,'$this->mov_responsable'
               ,'$this->mov_usualta'
               ,'$this->mov_fecalta'
               ,'$this->mov_descripcion'
               ,'" . numdec($this->mov_preciounitario, 2)."'
               ,'" . numdec($this->mov_cantvendida, 0)."'
               ,'" . numdec($this->mov_costocompra, 2)."'
               ,'$this->mov_tipoot'
               ,'$this->mov_sucursal' 
               ,'$this->mov_sucursaldestino' 
               ,'$this->mov_idweb'
               ,'" . numdec($this->mov_bonif, 0)."'                
            )";
      
            $datos = DB::insert($query_operacion);
        } catch (\Exception $e) {
           // dd ("Capturo el try del comprobante ", $e); 
            $this->ret =  $e->getMessage() ;
            return  $this->ret;
        }  
//          displaylog ($query_operacion);
            return  $this->ret ;
  } 

  public function images(){
      // Relacion Polimorfica, con muchas imagenes
        return $this->morphMany('App\Models\Image','imageable');
  }

  public function marca() {
        //Recupera la relacion  // Define la relacion  Muchos -> 1
        // Si no se espesifica el 2do parametro asume  perfil_id     nombrerelacion_id
        return $this->belongsTo('App\Models\marca','Prod_Marca'); 
  }

} // fin clase producto
