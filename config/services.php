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
            'usuario' => env('SERVICIO_OSDE_USUARIO', 'r_niella@hotmail.com  (Profesionales)'),
            'clave' => env('SERVICIO_OSDE_CLAVE', '2023bETO'),
        ],
        'SANCOR' => [
            'usuario' => env('SERVICIO_SANCOR_USUARIO', '600208'),
            'clave' => env('SERVICIO_SANCOR_CLAVE', '32541455'),
        ],
        'JERA' => [
            'usuario' => env('SERVICIO_JERA_USUARIO', 'CENTERFOTOOPTICA'),
            'clave' => env('SERVICIO_JERA_CLAVE', 'belenroge'),
        ],
        'OSPJN' => [
            'usuario' => env('SERVICIO_OSPJN_USUARIO', 'r_niella@hotmail.com'),
            'clave' => env('SERVICIO_OSPJN_CLAVE', 'belenroge'),
        ],
    ],

];
