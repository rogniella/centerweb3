<?php namespace App\Models;
use App\Models\minformecod;  

use Illuminate\Database\Eloquent\Model;

class minforme extends Model {


  protected $table = "minforme";
  protected $primaryKey =  'inf_idInforme';
  public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"

  protected $fillable = ['inf_tipo', 'inf_Descripcion','inf_info1','inf_info2']; // Campos que pueden ser accedidos


  public static function buscar($filtro_descripcion =''){

      // Se la define static  para llamarla sin objeto con :: 
        // Listado principal, dependiendo de los filtros

        $filter = " where 1=1";
        $valores = [];
        if ($filtro_descripcion != "") {
            $filter .= " AND inf_descripcion LIKE ?";
            $valores[] = '%' . $filtro_descripcion . '%';
        }

        $filter .=  " order by inf_descripcion";

        $consulta= "SELECT inf_idinforme,inf_tipo, inf_Descripcion,inf_info1,inf_info2, infTipo_Descripcion FROM minforme inner join minformetipo on  inf_tipo =  infTipo_Id  " . $filter ;


        $datos = \DB::select($consulta,$valores);

  
    return $datos;

  } // Fin Buscar

  public static function graba_codigos( $informe, $codigos){



    $consulta= "DELETE FROM minformecod WHERE infCod_idInforme = ?"  ;
    $datos = \DB::select($consulta, [$informe ]);


      foreach ($codigos as $elem) {
      //displaylog ( $elem  );
     // $codigos += [ "{$elem}" => 0];
     // $codigos ["{$elem}"]= 1; // Como rendimiento asumo 1
     //   dd( $codigos , $elem );
        $infcod = new Minformecod;
        $infcod->infCod_IdInforme = $informe;
        $infcod->infCod_Codigo = $elem;
        $infcod->infCod_Rendimiento = 1;
        $infcod->save();
      } 

       return "Actualizado";
  } // Fin 

  public static function graba_rendimiento( $id, $rendimiento){

    // Actualiza el rendimiento de 1 codigo para un determinado Informe
    // Para los Informes tipo 1
    $consulta= "UPDATE minformecod  SET infCod_Rendimiento = ? WHERE id = ?"  ;
    $datos = \DB::select($consulta, [ $rendimiento ,$id ]);
    return "Actualizado";

  } // Fin 
  
}
