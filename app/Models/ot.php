<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

use App\clases\correlativo;  
use App\Models\cliente;  // Modelos a utilizar
use App\Models\ot_ant;  
use App\Models\ot_cel;
use App\Models\ot_len;

class ot extends Model
{
    
    protected $table = "ot";
    // Difino Clave Primaria
	protected $primaryKey = 'Ot_idWEB';

    // Otros Atributos personalizados
    public $ArmazonLejos ='';
    public $ArmazonCerca ='';

    public $DatosLejos ='';
    public $DatosCerca ='';
    public $Tipo_Lentes ='';
    public $Clase_Lentes ='';

    public static function buscar($filtro_tipoinforme,$filtro_sucursal,$filtro_fecini ='',$filtro_fecfin ='',$filtro_tipoot =''
             ,$filtro_estado='') {

    	// Se la define static  para llamarla sin objeto con ::	
        // Listado principal, dependiendo de los filtros

        $filter = " where 1=1";
        $valores = [];
        if ($filtro_sucursal != "0") {
            $filter .= " AND Ot_Sucursal = ?";
            $valores[] = $filtro_sucursal ;
        }
        if ($filtro_tipoot != "") {
            $filter .= " AND Ot_Tipo = ?";
            $valores[] = $filtro_tipoot ;
        }
        if ($filtro_fecini != "") {
            $filtro_fecini = $filtro_fecini . " 00:00:00";
            $filtro_fecfin = $filtro_fecfin . " 23:59:59";
            $filter .= " AND Ot_FecPedido >= '" . $filtro_fecini . "' and Ot_FecPedido <= '". $filtro_fecfin . "' ";
        }

        switch ($filtro_tipoinforme) {
          case '':  // Completo
            if ($filtro_estado != "") {
                $filter .= " AND Ot_Estado = ?";
                $valores[] = $filtro_estado ;
            }else{  // Saco los anulados
                $filter .= " AND Ot_Estado <> ?";
                $valores[] = 'A' ;
            }        
            break;
          case 'A':  // Atrasados
            $hoy = fechahorahoy();
            $filter .= " AND Ot_FecPrometida < '" . $hoy . "'";       
            $filter .= " AND Ot_Estado <> 'A' AND Ot_Estado <> 'E' AND Ot_Estado <> 'L'";
            break;
          case 'P':  // Pendientes de Entrega
            $filter .= " AND Ot_Estado <> 'A' AND Ot_Estado <> 'E'";
            break;
        } //Fin Tipo Informe    

        $filter .=  " order by Ot_Id desc";

        $consulta = "SELECT Ot_idWEB,Ot_Sucursal, DATE_FORMAT(Ot_FecPedido , '%d/%m/%Y') as fecha 
         , DATE_FORMAT(Ot_FecPrometida , '%d/%m/%Y') as fechaprometida 
         ,Ot_Id, If(Ot_Tipo='A','Anteojo',If(Ot_Tipo='L','L.Contac',If(Ot_Tipo='C','Celu',If(Ot_Tipo='R','Repara',If(Ot_Tipo='G','Garantia',Ot_Tipo))))) As Tipo, Ot_Tipo, Ot_IdCli";

        $consulta.=", Cli_ApeNom, Ot_Vendedor, Ot_Precio, Ot_ObrId, Ot_Estado , ot_estados.Descripcion";
        $consulta.=" FROM  ot LEFT JOIN clientes ON CLI_ID = OT_IDCLI  LEFT JOIN ot_estados ON codigo = ot_estado " . $filter; 

        $datos = DB::select($consulta,$valores);
      //  dd($consulta, $datos,$valores);

		return $datos;

    } // Fin Buscar


    public static function find_suc_id ( $sucursal,$tipo, $id ){
        $datos = Ot::where('Ot_Sucursal', '=', $sucursal)->where('Ot_Tipo', '=', $tipo)->where('Ot_Id', '=', $id)->first();
        return $datos;
    }        

    public function Pagos()
    {
 
        $pago = 0;
        $aux_sucursal = $this->Ot_Sucursal;
        //Para los casos de Alvear
        if ($this->Ot_Sucursal == 11 or $this->Ot_Sucursal == 12 ) {
          $aux_sucursal = 1;
        }

        // Busco todos los pagos de la OT

        $consulta = "select SUM(caj_monto) as suma from caja where caj_sucursalOri = ? and caj_idot = ? and caj_tipoot = ? ";

        if ( $ret = DB::select($consulta , [$aux_sucursal,$this->Ot_Id,$this->Ot_Tipo])  ) {
            $pago = $ret[0]->suma;
        }
        
        return $pago;

    }
 
    public function DetallePagos()
    {
 
        // Busco todos los pagos de la OT
        $aux_sucursal = $this->Ot_Sucursal;
        if ($this->Ot_Sucursal == 11 or $this->Ot_Sucursal == 12 ) {
          $aux_sucursal = 1;
        }

        $consulta = "select * from caja where caj_sucursalOri = ? and caj_idot = ? and caj_tipoot = ? ";

        $ret = DB::select($consulta , [ $aux_sucursal,$this->Ot_Id,$this->Ot_Tipo])  ;
        
        return $ret;

    }

    public function DetalleProductos()
    {
 
      $aux_sucursal = $this->Ot_Sucursal;
      //Para los casos de Alvear
      if ($this->Ot_Sucursal == 11 or $this->Ot_Sucursal == 12 ) {
        $aux_sucursal = 1;
      }

        $consulta = "select * from moviproductos where Mov_Sucursal = ? and  Mov_IdOt = ? and Mov_TipoOT = ? ";

        $ret = DB::select($consulta , [ $aux_sucursal,$this->Ot_Id,$this->Ot_Tipo])  ;
        
        return $ret;

    }


    public static function find_idWEB($idWEB) {


        if  ( !$datos = Ot::where('Ot_idWEB', '=', $idWEB)->first() ) {
            return $datos ;
        }
        // Cargo la relacion , porque la automatica no nda por no ser el Id en Clientes    
      //  displaylog ("Busco Cli: " . $datos->Ot_IdCli); 
        if  ( ! $datos->Cliente =  cliente::find_id( $datos->Ot_IdCli ) ) {
            $datos->Cliente = new  cliente;
            $datos->Cliente->Cli_ApeNom = "Error al Buscar Cli Id:" . $datos->Ot_IdCli; 
        }

        switch ($datos->Ot_Tipo) {
          case ($datos->Ot_Tipo == 'A' || $datos->Ot_Tipo == 'G') :  // Anteojos Recetados
            if  ( ! $datos->DatosREC =  ot_ant::find_suc_id( $datos->Ot_Sucursal,$datos->Ot_Id ) ) {
                $datos->DatosREC = new  ot_ant;
            }
            break;
          case 'C':  // Celulares
            if  ( ! $datos->DatosCEL =  ot_cel::find_suc_id( $datos->Ot_Sucursal,$datos->Ot_Id ) ) {
                $datos->DatosREC = new  ot_cel;
            }
            break;
        } // End switch TipoOt

        return $datos;

    } // Fin find


    // Asi no funciona las relacions porque la tabla cliente tiene otro id   
    //public function Cliente()
    //{
    //    return $this->belongsTo('App\Cliente','Ot_IdCli'); 

    //}


    public function Descripcion_Estado($codigo)
    {
 
        $consulta = "select descripcion from ot_estados where codigo = '$codigo'";

        if ( $ret = DB::select($consulta) ) {
            return $ret[0]->descripcion;
        }else{
            return "Error NO se encontro Estado:" . $codigo;
        }
    }

    public function Descripcion_Tipo_Lentes($tipo)
    {
 
   //     dd($tipo,$this->DatosREC->OtAnt_Bifocal);
        switch ($tipo) {
          case '': 
            return "Separados";
            break;
          case 'S': 
            return "Separados Stock";
            break;
          case 'L': 
            return "Laboratorio";
            break;
          case 'B': 
            return "Bifocales";
            break;
          case 'M': 
            return "Multifocales";
            break;
        }      

        return $tipo;

    }


    public function Descripcion_Tipo_Ot($tipo)
    {
 
     switch ($tipo ) {
      case 'A':  // Anteojos Recetados
        return "ANTEOJOS";
        break;
      case 'L':  
        return "LENTES CONTACTO";
        break;
      case 'C':  
        return "CELULARES";
        break;
      case 'G':  
        return  "ANTEOJOS POR GARANTIA";
        break;
     } // End switch TipoOt
     return "VER TIPO DE ORDEN";

    }

} //Fin del Modelo
