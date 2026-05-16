<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\marca;  
use Illuminate\Support\Facades\DB;  // Para usar SQL directamente (Raw SQL)

class MarcasController extends Controller
{
    
public function combo_marca( Request $request )
  {
    
    // Completa segun la familia Seleccionada
    
    if ( $request->incluyeTodas == 'S' ) {
      $html = '<option value= "">[Todas Marcas]</option>';
      $html .= '<option value= "0">-Sin Clasificar-</option>';
    } else {
      $html = '<option value= "0">[Sin Clasificar]</option>';
    } 
  
    $consulta = "SELECT  id , nombre  FROM marcas where familia = ? and estado <>'I'";

    $ret = DB::select($consulta ,[$request->familia]);
    foreach ($ret as $objelem) {
      $row = (array) $objelem ;  // Para adaptar a la vs que ya tenia
      if( $row["id"] == $request->marca ) {
        $html .= '<option value= "' . $row["id"] . '"selected>' . $row["nombre"] . '</option>';
      }else{
        $html .= '<option value= "' . $row["id"] . '">' .  $row["nombre"] . '</option>';
      }         
    }
  
    $respuesta = array("html"=>$html);
    echo json_encode($respuesta);

  } // Fin combo_marca

} // Fin de la Clase
