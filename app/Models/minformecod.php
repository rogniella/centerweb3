<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class minformecod extends Model {


  protected $table = "minformecod";
  protected $primaryKey =  'id';
  public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"

  protected $fillable = ['infCod_IdInforme', 'infCod_Codigo','infCod_Rendimiento']; // Campos que pueden ser accedidos
  
  public static function buscar($filtro_informe =''){

          // Listado principal, dependiendo de los filtros

          $filter = " where 1=1";
          $valores = [];
          if ($filtro_informe != "") {
              $filter .= " AND infCod_IdInforme = ?";
              $valores[] =  $filtro_informe ;
          }
  
  
          $consulta= "SELECT id,  infCod_IdInforme, infCod_Codigo , infCod_Rendimiento ,  MCod_Descripcion,MCOD_HYD  FROM minformecod inner join mcodigo On  infCod_Codigo =  MCod_Codigo " . $filter ;
  
  
          $datos = \DB::select($consulta,$valores);
  
    
      return $datos;
  
    } // Fin Buscar
  
}
