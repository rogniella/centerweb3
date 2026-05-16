<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\clases\correlativo;  


class cliente extends Model
{
    
    protected $table = "clientes";
	protected $primaryKey = 'Cli_idWEB';     // Defino Clave Primaria
    
    // Campos que pueden ser accedidos y modificados
    protected $fillable = ['Cli_Id','Cli_ApeNom','Cli_Documento','Cli_Telefono', 'Cli_Pais',
     'Cli_Calle', 'Cli_CodRespIVA','Cli_Cuil','Cli_CodDocumento'];

    public function ots() {
        // Define la relacion  1 -> Muchos
        //return $this->hasMany('App\Article', 'nombre_clave_foranea', 'nombre_clave_primaria_local');
        return $this->hasMany('App\Models\ot' ,'Ot_IdCli','Cli_Id')->orderBy('Ot_FecPedido', 'DESC'); 
    }

    public function ventas() {
        // Define la relacion  1 -> Muchos
        //return $this->hasMany('App\Article', 'nombre_clave_foranea', 'nombre_clave_primaria_local');
        return $this->hasMany('App\Models\comprobantebd' ,'Comp_IdCli','Cli_Id')->orderBy('Comp_FecMov', 'DESC'); 
    }
   
    public static function buscarRI(){

        // Se la define static  para llamarla sin objeto con :: 
        // Busqueda de AutoCompletar y Listado principal, dependiendo de los filtros
        // Concatenar según la consulta. Armo scrip de consulta con los campos segun columnas de la pantalla principal

 //arreglo 1       $filter = " where Cli_CodRespIVA = 'RI'";
      $filter = " where cli_coddocumento = 'CUIT' or cli_coddocumento = 'CUIL'";

        $consulta= "SELECT  cli_apenom,cli_coddocumento,cli_documento,cli_cuil, cli_telefono,cli_id  FROM clientes " . $filter ;

        $datos = DB::select($consulta);


        return $datos;

    } // Fin Buscar

    public static function buscar($filtro_apenom ='',$filtro_sucursal ='',$limite = 1000){

    	// Se la define static  para llamarla sin objeto con ::	
        // Busqueda de AutoCompletar y Listado principal, dependiendo de los filtros
        // Concatenar según la consulta. Armo scrip de consulta con los campos segun columnas de la pantalla principal

        $filter = " where 1=1";
        $valores = [];
        if ($filtro_apenom != "") {
            if (is_numeric($filtro_apenom)) {
                $filter .= " AND Cli_Documento LIKE ?";
            }else{
                $filter .= " AND Cli_ApeNom LIKE ?";
            }    
            $valores[] = '%' . $filtro_apenom . '%';
        }

        if ( is_numeric($filtro_sucursal) ) {
            $filter .= " AND cli_sucursal = ?";
            $valores[] =  $filtro_sucursal ;
        }

        $filter .=  " order by Cli_ApeNom";


        $consulta= "SELECT cli_sucursal,cli_apenom,cli_coddocumento,cli_documento,cli_cuil, cli_codrespiva , CONCAT( cli_pais , ' ' ,  cli_telefono ) as cli_telefono ,cli_calle, cli_idWEB,cli_id  FROM clientes " . $filter ;

        $datos = DB::select($consulta,$valores);


		return $datos;

    } // Fin Buscar

    public function valida_delete_id($id){

        // Se utiliza antes de Eliminar  por Id
        // Primero Validar si no tiene Releciomes Activas con otras tablas
        // Si tiene algo retorna un msg , sino el msg queda en blanco

        $ret = "";    

        $query_operacion = "SELECT count(Ot_Id) as cantidad FROM ot  WHERE    Ot_IdCLI = {$id}";
        $resultado = DB::select($query_operacion); 
        if (isset( $resultado[0] )  AND $resultado[0]->cantidad > 0  ) {
            $ret = "Cliente Tiene " .  $resultado[0]->cantidad  . " OT  ingresadas";           
            return $ret; // Sale ya encontro no sigo buscando
        }       

        $query_operacion = "SELECT count(Comp_Id) as cantidad FROM comprobantes  WHERE    comp_IdCLI= {$id}";
        $resultado = DB::select($query_operacion); 
        if (isset( $resultado[0] ) AND $resultado[0]->cantidad > 0  ) {
            $ret = "Cliente Tiene " .  $resultado[0]->cantidad  . " Ventas  ingresadas";
            return $ret; // Sale ya encontro no sigo buscando
        }        

        $query_operacion = "SELECT count(MDes_IdProv)  as cantidad FROM mcaja  WHERE    MDes_IdProv= {$id} * -1";
        $resultado = DB::select($query_operacion); 
        if (isset( $resultado[0] ) AND $resultado[0]->cantidad > 0  ) {
            $ret = "Cliente Tiene " .  $resultado[0]->cantidad  . "  movimientos en la Cta Corriente";
            return $ret; // Sale ya encontro no sigo buscando
        }        

        return $ret;          
    } // Fin Valida_Delete

    public function delete_id($id ,  $id_nuevo ){

        // Elimina por Id SUC

        // Primero Validar si no tiene Releciomes Activas con otra tabla 
        //  Si tiene  $cli_id_nuevo  tiene que ser <> 0

        if ($id_nuevo <> 0) {
            // Reasigno todas las relaciones, antes de eliminar
            $query_operacion = "UPDATE ot SET  Ot_IdCli = {$id_nuevo} WHERE   Ot_IdCli= {$id}";
            $resultado = DB::update($query_operacion); 

            $query_operacion = "UPDATE comprobantes   SET  comp_IdCli = {$id_nuevo} WHERE   comp_IdCli= {$id}";
            $resultado = DB::update($query_operacion); 


         //   $query_operacion = "UPDATE atencion   SET  ate_IdCli = {$id_nuevo} WHERE   ate_IdCli= {$id}";
         //   $resultado = DB::update($query_operacion); 


            $query_operacion = "UPDATE mcaja  SET  MDes_IdProv = {$id_nuevo} * -1 WHERE   MDes_IdProv= {$id} * -1";
            $resultado = DB::update($query_operacion); 

        }        

        return parent::delete();

    } // Fin Delete


    public static function find_idWEB($id ){

        // Se la define static  para llamarla sin objeto con :: 

        $datos = cliente::where('cli_idweb', '=', $id)->first();

        return $datos;

    } // Fin


    public static function find_id($id ){

        // Se la define static  para llamarla sin objeto con :: 

        $datos = cliente::where('cli_id', '=', $id)->first();

        return $datos;

    } // Fin

    public static function find_suc_id($sucursal,$id ){

        // Se la define static  para llamarla sin objeto con :: 

        //no ya tiene que estar el id correcto $idcli =  $id + ( $sucursal * 1000000 );

        $datos = cliente::where('cli_sucursal', '=', $sucursal)->where('cli_id', '=', $id)->first();

        return $datos;

    } // Fin

/*
    public function save2(array $options = array()) {

        if( $this->Cli_CodDocumento == "" ) {
            // lo completo segun el largo del documento
            if( strlen ($this->Cli_Documento) == 11 ) {
                $this->Cli_CodDocumento = "CUIT";
            }else{
                $this->Cli_CodDocumento = "DNI";
            }
        }

        // Si la instancia ya está en base de datos, es un Update, sino es un Insert
        if ($this->exists) {
            //Update
            $this->Cli_FecUltMan = fechahorahoy();       
        }else{
            // Insert 
            $this->Cli_Id = 0 ;
            $this->Cli_FecAlta = fechahorahoy();       
        }    

	    return parent::save($options );

    }
*/

} //Fin del Modulo