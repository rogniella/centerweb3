@extends('template.main')
@section('titulo','Actualización de Precios')
   
@section('contenido')



@endsection <!-- Fin Contenido -->


<?php
include("../cabecera.php");

require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//falta use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
//use PhpOffice\PhpSpreadsheet\IOFactory;

include_once ("../config.php");
require_once TOOLS_CLASS.'database.php';

require_once '../class/producto.php';

$conexion = new database('comercio');
if ( $conexion->sqlmsg <> "") {
    echo $conexion->sqlmsg  ;
    exit;
} 	

try {

    $filename = "../template/lista_precios.xlsx";
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $spreadsheet = $reader->load($filename);
    //$sheet = $spreadsheet->setActiveSheetIndexByName('mauro');
    echo "Usar https://cloudconvert.com/xlsx-to-jpg   para convertir a Imagen <br>";
  
    echo "Hoja 1 Stock: <br>";     
    $sheet = $spreadsheet->setActiveSheetIndex(0); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "S";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "T";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      proceso_columna($sheet,$conexion,4,38,$letraCod,$letraVal,$letraCos);

    }  //  End For j     
    
    echo "Hoja 4 Bifocales: <br>";     
    $sheet = $spreadsheet->setActiveSheetIndex(3); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "T";
            $letraVal = "E";
            $letraCos = "H";
            break;
        case '2': // Stratus
            $letraCod = "U";
            $letraVal = "F";
            $letraCos = "I";
            break;       
      } // fin switch 
      proceso_columna($sheet,$conexion,4,22,$letraCod,$letraVal,$letraCos);

    }  //  End For j     

    echo "Hoja 5 Multifocales: <br>";     
    $sheet = $spreadsheet->setActiveSheetIndex(4); // de MULTIFOCALES
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "S";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "T";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      proceso_columna($sheet,$conexion,5,30,$letraCod,$letraVal,$letraCos);

    }  //  End For j     
    
    echo "Hoja 6 Varilux: <br>";     
    $sheet = $spreadsheet->setActiveSheetIndex(5); // de varilux
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 5; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "W";
            $letraVal = "C";
            $letraCos = "R";
            break;
        case '2': // Stratus
            $letraCod = "X";
            $letraVal = "D";
            $letraCos = "S";
            break;
        case '3': // Alize Uv 
            $letraCod = "Y";
            $letraVal = "E";
            $letraCos = "T";
            break;
        case '4': // Prevencia 
            $letraCod = "Z";
            $letraVal = "F";
            $letraCos = "U";
            break;
        case '5': // Blue Filter 
            $letraCod = "AA";
            $letraVal = "G";
            $letraCos = "V";
            break;
       
      } // fin switch 
      proceso_columna($sheet,$conexion,6,30,$letraCod,$letraVal,$letraCos);
    }  //  End For j     
  
    echo "Hoja 7 Lentes de Contacto: <br>";     
    $sheet = $spreadsheet->setActiveSheetIndex(6); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "Q";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "R";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      proceso_columna($sheet,$conexion,5,34,$letraCod,$letraVal,$letraCos,'LC');

    }  //  End For
    
    echo "Hoja 8 Laboratorio: <br>";     
    $sheet = $spreadsheet->setActiveSheetIndex(7); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "S";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "T";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      proceso_columna($sheet,$conexion,4,36,$letraCod,$letraVal,$letraCos);

    }  //  End For j     
    
    echo "Hoja 9 Laboratorio FOTO: <br>";     
    $sheet = $spreadsheet->setActiveSheetIndex(8); 
    // Las columnas que tiene los codigos        
    for ($j = 1; $j <= 2; $j++) {
     switch ($j) {
        case '1': //sin ar
            $letraCod = "S";
            $letraVal = "D";
            $letraCos = "G";
            break;
        case '2': // Stratus
            $letraCod = "T";
            $letraVal = "E";
            $letraCos = "H";
            break;       
      } // fin switch 
      proceso_columna($sheet,$conexion,4,15,$letraCod,$letraVal,$letraCos);

    }  //  End For j     
    
    
} catch (\Exception $e) {
    echo 'Ocurrio un error al intentar abrir el archivo Excel ' . $e;
}


function proceso_columna($sheet,$conexion,$filaini,$filafin,$letraCod,$letraVal,$letraCos,$familia ='LEN'){
    
          // Recorro las fialas que tiene datos
      for ($i = $filaini; $i <= $filafin; $i++) {
        $codigo =  trim($sheet->getCell($letraCod.$i)); //Retorna valor
        if($codigo <> "") {
            $precio =  $sheet->getCell($letraVal.$i)->getCalculatedValue(); //Retorna valor
            $costo =  $sheet->getCell($letraCos.$i)->getCalculatedValue(); //Retorna valor
            $precio2 = $precio * 0.9;  // Menos el 10%
            // Actuzalizar
            $oproducto = new producto();
            $oproducto->prod_familia = $familia;
            $oproducto->prod_id = $codigo;
            $oproducto->actualiza_precio($conexion,$precio,$precio2,$costo);
            if ($oproducto->ret <> '') {
                echo  "  " . $codigo . ": " . $precio . " Costo:" . $costo; 
                echo "  Error:" . $oproducto->ret . "<br>";
            }else{
                echo  "  " . $codigo . "  " . $oproducto->prod_descripcion .  "    Precio: " . $precio . " Costo:" . $costo; 
                if ($oproducto->retModifico == "S") {    
                    echo  "'<b> ACTUALIZADO </b>"; 
                }               
            } 
            echo "<br>";
        }
    }  //  End For Filas 
    
} // End proceso columna

?>
