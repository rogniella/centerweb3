<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)


class tar_operacion extends Model {

	protected $table = "tar_operaciones";
	public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"


    public static function findIdLiquidacion( $id ){


      $datos = tar_operaciones::where('idliquidacion', '=', $id)->get();
        
      return $datos;

    } // Fin findId



    public static function listar( $filtro_producto ='',$filtro_liquidacion ='',$filtro_fecha ='', $filtro_fechafin ='', $limite = 1000){

        // Listado principal, dependiendo de los filtros
        // Tambien lo utiliza el auto completar
        // Concatenar según la consulta. Armo scrip de consulta
        $filter = " where 1=1";
        $valores = [];

        if ($filtro_liquidacion != "") {
            $filter .= " AND  idliquidacion  = ?  ";
            $valores[] =  $filtro_liquidacion ;
        }    

        if ($filtro_producto != "") {
            $filter .= " AND producto  = ?  ";
            $valores[] =  $filtro_producto;
        }    

//        if ($filtro_estado != "") {
//            $filter .= " AND Fac_Estado = ?";
//            $valores[] =  formatoAccess($filtro_estado) ;
//        }


        if ($filtro_fecha != "") {
            $filtro_fecha = $filtro_fecha . " 00:00:00";
            $filtro_fechafin = $filtro_fechafin . " 23:59:59";
            $filter .= " AND fecha_clearing >= '" . $filtro_fecha . "' and fecha_clearing <= '". $filtro_fechafin . "' ";
        }        

//        $filter .=  " order by Fac_Id desc ";

        $consulta= "SELECT * FROM tar_operaciones LEFT JOIN tar_productos ON producto = tar_productos.id " . $filter  . " LIMIT $limite" ;

        $ret = DB::select($consulta,$valores);

        return $ret;                   

    } // Fin Listar




}
