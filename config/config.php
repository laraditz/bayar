<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'route_name' => 'bayar.',
    'route_prefix' => 'bayar',
    'middleware' => ['api'],
    'link_expires_in' => env('BAYAR_LINK_EXPIRES_IN', 5), // in minutes
    'link_visit_limit' => env('BAYAR_LINK_VISIT_LIMIT', 1),
];
