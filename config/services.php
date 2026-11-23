<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'servicios' => [
        'OSDE' => [
            'titulo' => env( 'SERVICIO_OSDE_TITULO', 'OSDE'),
            'ruta' => env('SERVICIO_OSDE_RUTA', 'https://extranet.osde.com.ar/OSDEExtranet/jsp/multiempresas/osde/HomePublicaV2.jsp'),
            'usuario' => env('SERVICIO_OSDE_USUARIO', 'r_niella@hotmail.com  (Profesionales)'),
            'clave' => env('SERVICIO_OSDE_CLAVE', '2023bETO'),
        ],
        'SANCOR' => [
            'titulo' => env('SERVICIO_SANCOR_TITULO', 'SANCOR SALUD'),
            'ruta' => env('SERVICIO_SANCOR_RUTA', 'https://autogestionprestadores.sancorsalud.com.ar'),
            'usuario' => env('SERVICIO_SANCOR_USUARIO', '600208'),
            'clave' => env('SERVICIO_SANCOR_CLAVE', '2026Beto*'),
        ],
        'JERA' => [
            'titulo' => env('SERVICIO_JERA_TITULO', 'JERARQUICOS SALUD'),
            'ruta' => env('SERVICIO_JERA_RUTA' , 'https://gestiones.jerarquicos.com/ConsumosOnline/Account/LogOn?ReturnUrl=%2fConsumosOnline%2fHome%2fIndex'),
            'usuario' => env('SERVICIO_JERA_USUARIO', 'CENTERFOTOOPTICA'),
            'clave' => env('SERVICIO_JERA_CLAVE', 'belenroge'),
        ],
        'OSPJN' => [
            'titulo' => env('SERVICIO_OSPJN_TITULO', 'PODER JUDICIAL'),
            'ruta' => env('SERVICIO_OSPJN_RUTA', 'https://extranet.ospjn.gov.ar/WebProveedores'),
            'usuario' => env('SERVICIO_OSPJN_USUARIO', 'r_niella@hotmail.com'),
            'clave' => env('SERVICIO_OSPJN_CLAVE', 'belenroge'),
        ],
    ],

];
