<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class publicacion extends Model {

	  protected $primaryKey = 'id';
	  protected $table = "publicaciones";

    public static function listar( $filtro_estado ='', $filtro_descripcion ='', $limite = 1000 ){

        // Listado principal, dependiendo de los filtros
        // Conencta con tablas productos, inventarios, marcas (es mas rapido en Sql que en programa)
        $filter = " where 1=1";
        $valores = [];

        if ($filtro_descripcion != "") {
            $filter .= " AND  ( prod_id  LIKE ? OR prod_descripcion  LIKE ? ) ";
            $valores[] = '%' . $filtro_descripcion . '%';
            $valores[] = '%' . $filtro_descripcion . '%';
        }

        switch ($filtro_estado) {
          case "T":
            // Si marco con T (es porque quiere todos los regitros)
            break;
          default: // Solo los Activos
            $filter .= " and estado = ?  ";
            $valores[] = $filtro_estado;
        }
        
        $filter .=  " order by prod_descripcion ";

        $consulta= "SELECT  id, precio_venta, observ,estado, prod_idweb, prod_familia, prod_id,prod_descripcion,prod_categoria,prod_costo,prod_precio,prod_precio2
          , (select Inv_Stock from inventarios where Inv_IdProd = prod_idweb and Inv_Sucursal = 1) as stock01
          , (select Inv_Stock from inventarios where Inv_IdProd = prod_idweb and Inv_Sucursal = 2) as stock02
          , IF(prod_marca>0, (select nombre from marcas where id = prod_marca) , '' ) as prod_marca2
          ,'' as tienda_name 
          ,0 as tienda_precio 
          , prod_fecultman ,  prod_marca , publicaciones.id FROM productos JOIN publicaciones ON Prod_idWEB = idWEB_prod " . $filter . " LIMIT $limite";
 
        $ret = DB::select($consulta,$valores);

        return $ret;                   

  } // Fin Listar

}
