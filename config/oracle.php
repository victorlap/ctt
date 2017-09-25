<?php

return [
    'oracle' => [
        'driver'         => 'oracle',
        'tns'            => env('ORACLE_TNS', ''),
        'host'           => env('ORACLE_HOST', ''),
        'port'           => env('ORACLE_PORT', '1521'),
        'database'       => env('ORACLE_DATABASE', ''),
        'username'       => env('ORACLE_USERNAME', ''),
        'password'       => env('ORACLE_PASSWORD', ''),
        'charset'        => env('ORACLE_CHARSET', 'AL32UTF8'),
        'prefix'         => env('ORACLE_PREFIX', ''),
        'prefix_schema'  => env('ORACLE_SCHEMA_PREFIX', ''),
        'server_version' => env('ORACLE_SERVER_VERSION', '11g'),
    ],
];
