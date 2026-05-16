<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ot_cel extends Model
{
    
    protected $table = "ot_cel";
    protected $primaryKey = 'OtCel_IdWEB';

    public static function find_suc_id ( $sucursal, $id ){

        // Se la define static  para llamarla sin objeto con :: 

        $datos = Ot_Cel::where('OtCel_Sucursal', '=', $sucursal)->where('OtCel_Id', '=', $id)->first();
     
        return $datos;

    } // Fin find

} //Fin del Modelo
