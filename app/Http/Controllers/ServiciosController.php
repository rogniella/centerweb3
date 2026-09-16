<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiciosController extends Controller
{
    public function index(Request $request)
    {

        // Los datos los toma del archivo config/serviciones.php

        $servicio = $request->input('msg'); //Ej OSDE , SANCOR ...
        $config = config("servicios.servicios.$servicio");

        if (! $config) {
            abort(404, 'Configuracion de Servicios no encontrado');
        }

        $datos = [
            'titulo' => $config['titulo'],
            'ruta' => $config['ruta'],
            'usuario' => $config['usuario'],
            'clave' => $config['clave'],
        ];

        return view('servicios.index')->with('datos', $datos);

    } // Fin Index

} // Fin Controler
