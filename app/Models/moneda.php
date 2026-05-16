<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class moneda extends Model {


	protected $table = "monedas";
	protected $primaryKey =  'Mon_Moneda';
  public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"

  protected $fillable = ['Mon_Descripcion','Mon_CodNum']; // Campos que pueden ser accedidos


  public static function buscar($filtro_descripcion =''){

      // Se la define static  para llamarla sin objeto con :: 
        // Listado principal, dependiendo de los filtros

        $filter = " where 1=1";
        $valores = [];
        if ($filtro_descripcion != "") {
            $filter .= " AND mon_descripcion LIKE ?";
            $valores[] = '%' . $filtro_descripcion . '%';
        }

        $filter .=  " order by mon_descripcion";

        $consulta= "SELECT mon_moneda,mon_descripcion,mon_codnum,mon_estado  FROM monedas " . $filter ;


        $datos = \DB::select($consulta,$valores);

  
    return $datos;

  } // Fin Buscar

  public static function comboLista (){

      // Carga lista de Sucursales segun el tipo

    	 $datos = moneda::where('mon_estado','=', 'L')->orderBy('mon_descripcion', 'ASC')->pluck( 'mon_descripcion','mon_moneda');
      	            
      return $datos;

  } // Fin 

}
