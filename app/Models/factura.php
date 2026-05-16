<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)


class factura extends Model
{
    
    protected $table = "facturas";
    // Difino Clave Primaria
	protected $primaryKey = 'Fac_idWEB';

    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"
    protected $fillable = ['Fac_IdOT','Fac_RazonSocial']; // Campos que pueden ser accedidos



    public static function listar( $filtro_punto ='', $filtro_tipo ='',$filtro_razon ='',$filtro_fecha ='', $filtro_fechafin ='', $limite = 1000){

        // Listado principal, dependiendo de los filtros
        // Tambien lo utiliza el auto completar
        // Concatenar según la consulta. Armo scrip de consulta
        $filter = " where 1=1";
        $valores = [];

        if ($filtro_punto != "") {
            $filter .= " AND  Fac_NroPuntoVta  = ?  ";
            $valores[] =  $filtro_punto ;
        }    

        if ($filtro_tipo != "") {
            $filter .= " AND  Fac_Comprobante =  ?  ";
            $valores[] =  $filtro_tipo ;
        }    

//        if ($filtro_estado != "") {
//            $filter .= " AND Fac_Estado = ?";
//            $valores[] =  formatoAccess($filtro_estado) ;
//        }

        if ($filtro_razon != "") {
            $filter .= " AND  Fac_RazonSocial  LIKE  ?  ";
            $valores[] = '%' . $filtro_razon . '%';
        }

        if ($filtro_fecha != "") {
            $filtro_fecha = $filtro_fecha . " 00:00:00";
            $filtro_fechafin = $filtro_fechafin . " 23:59:59";
            $filter .= " AND Fac_Fecha >= '" . $filtro_fecha . "' and Fac_Fecha <= '". $filtro_fechafin . "' ";
        }        

        $filter .=  " order by Fac_Id desc ";

        $consulta= "SELECT * , DATE_FORMAT(Fac_Fecha , '%d/%m/%Y') as fecha   FROM facturas " . $filter  . " LIMIT $limite" ;

        $ret = DB::select($consulta,$valores);

        return $ret;                   

    } // Fin Listar


    public static function findIdComprobante($tipoOT, $id ,$suc ){

      // Busca por Id , como tiene clave compuesta tengo que redefinir

      $datos = factura::where('Fac_IdOT', '=', $id)->where('Fac_TipoOT', '=', $tipoOT)->where('Fac_Sucursal', '=', $suc)
            ->first();
        
      return $datos;

    } // Fin findIdComprobante

    public function save(array $options = array()) {

        $this->Fac_FecAlta = fechahorahoy();               
        $this->Fac_FecUltMan = fechahorahoy();               

	    return parent::save($options );

    }

    protected $attributes = [
        // Para definir los valores por defecto
        'Fac_Bonif' => 0,
        'Fac_BonImp' => 0,
        'Fac_OtrosConceptos' => 0,
        'Fac_Exento' => 0,
        'Fac_TasaIva' => 0.21,
        'Fac_TasaIva1' => 0.105,
        'Fac_TasaIVANoIns' => 0,
        'Fac_IVAInscrip' => 0,     
        'Fac_IVAInscrip1' => 0,
        'Fac_IVANoInscrip' => 0,
        'Fac_PerContable' => 0
    ];

 

} //Fin del Modelo
