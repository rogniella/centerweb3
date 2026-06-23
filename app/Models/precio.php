<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class precio extends Model
{
    protected $primaryKey = 'id';

    protected $table = 'precios';

    protected $guarded = [];

    public static function find_producto($idProd)
    {

        $datos = Precio::where('idWEB_prod', '=', $idProd)->first();

        return $datos;

    } // Fin find

    public static function find_moneda($idMoneda)
    {

        $datos = Precio::where('idLista', '=', $idMoneda)->get();

        return $datos;

    } // Fin find

}
