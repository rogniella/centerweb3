<?php

function validarCUIT($cuit) {

    $ret = "";
    $cuit = preg_replace('/[^0-9]/', '', $cuit); // Eliminar caracteres no numéricos
    
    if (strlen($cuit) !== 11) {
        return "El CUIT debe tener 11 digitos";
    }
    
    $prefijo =  substr($cuit, 0, 2);  

    if ($prefijo == 20 Or $prefijo == 23 Or $prefijo == 24 Or $prefijo == 27 Or $prefijo == 30 Or $prefijo == 33 Or $prefijo == 34) {
        // Ok
    }else{    
        return "Error: CUIT/CUIL Incorrecto     Prefijos válidos: 20,23,24,27 o 30,33,34";
    }

    $coeficientes = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2); // Coeficientes de validación
    
    $suma = 0;
    for ($i = 0; $i < 10; $i++) {
        $suma += $cuit[$i] * $coeficientes[$i];
    }
    
    $verificador = 11 - ($suma % 11);
    if ($verificador === 11) {
        $verificador = 0;
    }
    
    if ( $cuit[10] == $verificador) {
        return ""; //Ok
    }else{
        return "Error:Digito del Cuit Invalido, El correcto:" . $verificador ; //Ok
    } 
    
} //Fin Valida Cuit

function calculoSucursal ($ot) {
    
    // Dependiendo de la Ot calculo la sucursal
        if($ot > 70000) {
            return 1;
        }else{
            return 2;
        }      
}


function redondear_a_10($valor , $tipo_redondeo = "-1") {

    // Convertimos $valor a entero
   // $valor = intval($valor);

    // Convertimos $valor a numerico con dec
    $valor = floatval($valor);

    // round(float $val, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): float
    // precision especificada (número de dígitos desde el punto decimal). precision puede también ser negativo o cero (valor predeterminado)
    // Redondeamos al múltiplo de 10 más cercano
    switch ( $tipo_redondeo ) {
        case "5": // nn00/50
            $n = round($valor, 2 ,PHP_ROUND_HALF_UP );
            return $n;
        default:
            $n = round($valor, intval( $tipo_redondeo ) ,PHP_ROUND_HALF_UP );
            return $n;
        }        
    // Si el resultado $n es menor, quiere decir que redondeo hacia abajo
    // por lo tanto sumamos 10. Si no, lo devolvemos así.
    return $n < $valor ? $n + 10 : $n;
}

function ejecutarSP($sql, $arrParams) {
            
            // Utilizada para ejecutar Store Procedu    

      //  Armo Cadena de parametros
        //    Con esto no me funciona   $resultado->bindValue($param, $value);

  //  foreach ($arrParams as $param => $value) {
  //    //  Vinculamos cada parametro (key) con el value del mismo.
  //    displaylog( $param .  $value);
  //  }

 
      $parametros= "(";
      $aux ="";
      foreach ($arrParams as $f) {
          $parametros .= $aux . "'".$f ."'" ;
          $aux =",";
      }
      $parametros .= ")";
                $sql .=  $parametros;

///      $sql="InsertMovDetallado('2018-05-04','0032','P','10,14','10','Ran','2018-05-04 7:50 am','01','','0','','prueba2 10,14','','','2018-05-04 7:59 am','','','0','0','CA')";

      displaylog("EjecutoSP:" . $sql);

            return "{call $sql}";
 
}






function numdec ($num  ,$cantdec =0) {
  
    // La Cantidad de decimales es opcional
    
    // string number_format ( float $number , int $decimals = 0 , string $dec_point = "." , string $thousands_sep = "," )
    //     Por defecto esta funcion usa formato ingles
        
    // localeconv() devuelve informaci├│n basada en la localidad actual, tal y como haya sido definida mediante setlocale(). La matriz asociativa que devuelve contiene los siguientes campos:
    //     
    //     
    //     
//  return number_format($num, $cantdec);
    
        //    $locale = localeconv();
       // displaylog( 'Dec:' . $locale['decimal_point']);
       // displaylog( 'miles:' . $locale['thousands_sep']);

//            return number_format($num, $cantdec, SEPARADOR_DEC,"");

   // Falta detectar si no es un nro
    if ( $num == "") { $num=0; }

//   $num =  str_replace(",",".",$num);
    try { 
        return number_format($num, $cantdec, '.',"");
    } catch (\Exception $e) {    
        displaylog ("Error al convertir Numero " . $num . " Error:" . $e );
        return 0;
    }

}

function limpiacaracteres ($texto) {

  // le saca los caracteres malos (escapar caracteres depende de la Bd)
  // no soportado en PHP7  $this->$var = Mysql_real_escape_string ($valor);
  // No se puede usar con odbc   $this->$var = $conexion->quote($valor);
    
  // 1) Sina nada no da error pero deja mal  
        //$texto =  str_replace("'","''",$texto); // Para access
//        return iconv("UTF-8","Windows-1254",$texto); // Para access

  //no da error pero deja mal      return iconv("Windows-1254","UTF-8",$texto); // Para access


        $texto =  addslashes($texto); // Para access  le agrega / para salvar los caracters
        $texto =  str_replace("\'","''",$texto); // Para access para arreglar '
        

    //    return iconv("UTF-8","Windows-1254",$texto); // Para access

        return $texto;

}


function displaylog($msg){
    
    // Usa   Monolog    
    // LOG DE ERRORES EN LA CARPETA  /storage/logs/laravel-fehca.log    
    Log::info($msg);

}//end funcion displaylog


function fechahorahoy () {
    
    // Retorna fecha y hora actual con formato acorde para guardar en Base de Datos
    // return date("Y-m-d g:i a"); // Para Access
    return date('Y-m-d H:i:s');  // Para MySQL   
}

function fechahoy () {
    
    // Retorna fecha actual con formato acorde para guardar en Base de Datos
    return date("Y-m-d");     
}


function lineaDescriGrado ($nombre,$esf,$cil,$grad) {
    
    if ( $esf == 0 and $cil == 0 ) {return '<b>' . $nombre . ":</b>"; } // Si no tiene cristal

    $txtcil = "";
    if ( $cil !=0 ) {
        $txtsigno = "";
        if ( $cil  > 0 ) {$txtsigno = "+";}
        $txtcil = " Cil " . $txtsigno . number_format($cil,2) . " en " . $grad . "°";
    }
    $txtsigno = "";
    if ( $esf > 0 ) {$txtsigno = "+";}
    if ( $esf == 99 ) {$esf = 0;} // Neutro
    
    return '<b>' . $nombre . ":</b>  Esf " . $txtsigno . number_format($esf,2)  .  $txtcil ;

}


?>
