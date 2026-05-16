<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class inventario extends Model
{
    
  protected $table = "inventarios";
	protected $primaryKey = 'Inv_idWEB';
    
  public static function findCodigo($id , $sucursal){

      $datos = Inventario::where('Inv_IdProd', '=', $id)->where('Inv_Sucursal', '=', $sucursal)
            ->first();
      return $datos;

  } // Fin find


} //Fin del Modulo