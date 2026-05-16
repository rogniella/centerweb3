<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class ComprasProv extends Model
{
    
	protected $connection = 'bdcomercio';
    protected $table = "ComprasProv";
    // Difino Clave Primaria
	protected $primaryKey = 'Cprov_Id';
      public $incrementing = false; // Por ser de access que no funciona lastInsertId()
    // Con Access no permite porque es otro tipo de datos
    public $timestamps = false;  // Esta tabla no tiene los campos Timestamps "created_at" y "updated_at"
    protected $fillable = ['Cprov_IdProv','Cprov_RazonSocial']; // Campos que pueden ser accedidos



  public static function listar( $filtro_punto ='', $filtro_tipo ='',$filtro_razon ='',$filtro_fecha ='', $filtro_fechafin ='', $limite = 1000){
        // Listado principal, dependiendo de los filtros
        // Tambien lo utiliza el auto completar
        // Concatenar según la consulta. Armo scrip de consulta
        $filter = " where 1=1";
        $valores = [];

        if ($filtro_punto != "") {
            $filter .= " AND  Fac_NroPuntoVta  LIKE ?  ";
            $valores[] = '%' . $filtro_punto . '%';
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
            $filter .= " AND  Fac_RazonSocial  LIKE ?  ";
            $valores[] = '%' . formatoAccess($filtro_razon) . '%';
        }

        if ($filtro_fecha != "") {
            $filter .= " AND  Fac_Fecha >= DateValue ( ? ) and Fac_Fecha  <= DateValue ( ? ) ";
            $valores[] = $filtro_fecha;
            $valores[] = $filtro_fechafin;
        }        

        $filter .=  " order by Fac_Id desc ";

        $consulta= "SELECT top $limite  Format(Fac_Fecha, 'dd-mm-yyyy') as fecha, *  FROM Facturas " . $filter ;

        $ret = DB::connection('bdcomercio')->select($consulta,$valores);

        // Convertir caracteres especiales, sino da error al hacer json
        $ret   = convert_from_latin1_to_utf8_recursively($ret);            

        return $ret;                   

  } // Fin Listar


    public function save(array $options = array()) {

        $this->Cprov_FecAlta = fechahorahoy();               
        $this->Cprov_FecUltMan = fechahorahoy();               

        // filtrar lo caracteres especiales los campo que correspondan
        $this->attributes = formatoAccess($this->attributes);

	    return parent::save($options );

    }

    protected $attributes = [
        // Para definir los valores por defecto
        'Cprov_TasaIvaIns' => 0.21,
        'Cprov_TasaIvaIns1' => 0
    ];

 

} //Fin del Modelos
