<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiciosController extends Controller
{
    public function index(Request $request)
    {

        $servicio = $request->input('msg');
        $config = config("servicios.servicios.$servicio");

        if (! $config) {
            abort(404, 'Servicio no encontrado');
        }

        $rutas = [
            'OSDE' => 'https://extranet.osde.com.ar/OSDEExtranet/jsp/multiempresas/osde/HomePublicaV2.jsp',
            'SANCOR' => 'https://autogestionprestadores.sancorsalud.com.ar',
            'JERA' => 'https://gestiones.jerarquicos.com/ConsumosOnline/Account/LogOn?ReturnUrl=%2fConsumosOnline%2fHome%2fIndex',
            'OSPJN' => 'https://extranet.ospjn.gov.ar/WebProveedores',
        ];

        $titulos = [
            'OSDE' => 'OSDE',
            'SANCOR' => 'SANCOR SALUD',
            'JERA' => 'JERARQUICOS SALUD',
            'OSPJN' => 'PODER JUDICIAL',
        ];

        $datos = [
            'titulo' => $titulos[$servicio],
            'ruta' => $rutas[$servicio],
            'usuario' => $config['usuario'],
            'clave' => $config['clave'],
        ];

        return view('servicios.index')->with('datos', $datos);

    } // Fin Index

} // Fin Controler
