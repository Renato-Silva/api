<?php

declare(strict_types=1);

return [

    'prociv' => [
        'hostname' => env('PROCIV_HOSTNAME'),
    ],
    'googleapi' => [
        'hostname' => env('GOOGLEAPI_HOSTNAME', 'https://maps.googleapis.com/maps/api'),
    ],

];
