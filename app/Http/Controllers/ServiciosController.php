<?php namespace App\Http\Controllers;

class ServiciosController extends Controller {

	public function index()
	{

        $servicio = $_GET["msg"];
        switch ($servicio) {
          case "OSDE":
            $titulo = "OSDE" ; 
            $ruta = "https://extranet.osde.com.ar/OSDEExtranet/jsp/multiempresas/osde/HomePublicaV2.jsp"; 
            $usuario = "r_niella@hotmail.com  (Profesionales)"; 
            $clave = "2023bETO"; 
            break;
          case "SANCOR":
            $titulo = "SANCOR SALUD"; 
            //$ruta = "http://www.sancorsalud.com.ar/";
            $ruta = "https://autogestionprestadores.sancorsalud.com.ar";
            $usuario = "600208"; 
            $clave = "32541455"; 
            break;
          case "JERA":
            $titulo = "JERARQUICOS SALUD" ; 
            $ruta = "https://gestiones.jerarquicos.com/ConsumosOnline/Account/LogOn?ReturnUrl=%2fConsumosOnline%2fHome%2fIndex"; 
            $usuario = "CENTERFOTOOPTICA"; 
            $clave = "belenroge"; 
            break;
          case "OSPJN":
              $titulo = "PODER JUDICIAL" ; 
              $ruta = "https://extranet.ospjn.gov.ar/WebProveedores"; 
              $usuario = "r_niella@hotmail.com"; 
              $clave = "belenroge"; 
              break;
        
	  	}      

      	$datos = [
            'titulo' => $titulo,
            'ruta' => $ruta,
            'usuario' => $usuario,
            'clave' => $clave
      	];

	  return view('servicios.index')->with('datos', $datos);		

	} // Fin Index

} // Fin Controler
