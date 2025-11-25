<?php

// config for SmartDato/Dpd
return [
    /*
    |--------------------------------------------------------------------------
    | DPD Environment
    |--------------------------------------------------------------------------
    |
    | Determines whether to use staging or production endpoints.
    | Options: 'staging', 'production'
    |
    */
    'environment' => env('DPD_ENVIRONMENT', 'staging'),

    /*
    |--------------------------------------------------------------------------
    | DPD Credentials
    |--------------------------------------------------------------------------
    |
    | Your DPD API credentials. The delisId is your customer ID and the
    | password is provided by DPD. Never commit these to version control.
    |
    */
    'credentials' => [
        'delis_id' => env('DPD_DELIS_ID'),
        'password' => env('DPD_PASSWORD'),
        'sending_depot' => env('DPD_SENDING_DEPOT'), // 4-digit depot code (optional, defaults to first 4 digits of delis_id)
    ],

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    |
    | SOAP WSDL endpoints for different environments.
    |
    */
    'endpoints' => [
        'staging' => [
            'login' => 'https://public-ws-stage.dpd.com/services/LoginService/V2_0?wsdl',
            'shipment' => 'https://public-ws-stage.dpd.com/services/ShipmentService/V4_5?wsdl',
            'parcel_lifecycle' => 'https://public-ws-stage.dpd.com/services/ParcelLifeCycleService/V2_0?wsdl',
        ],
        'production' => [
            'login' => 'https://public-ws.dpd.com/services/LoginService/V2_0?wsdl',
            'shipment' => 'https://public-ws.dpd.com/services/ShipmentService/V4_5?wsdl',
            'parcel_lifecycle' => 'https://public-ws.dpd.com/services/ParcelLifeCycleService/V2_0?wsdl',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for authentication token caching. Tokens are valid for 24 hours.
    |
    */
    'cache' => [
        'store' => env('DPD_CACHE_STORE', null), // null = default cache store
        'prefix' => 'dpd_auth',
        'ttl' => 86400, // 24 hours in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | SOAP Options
    |--------------------------------------------------------------------------
    |
    | Additional options passed to PHP's SoapClient constructor.
    |
    */
    'soap' => [
        'trace' => env('DPD_SOAP_TRACE', true),
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_MEMORY,
        'connection_timeout' => 30,
        'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | DPD rate limits: 30 labels/minute, 60 API calls/minute
    |
    */
    'rate_limits' => [
        'labels_per_minute' => 30,
        'calls_per_minute' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | Default settings for shipment creation
    |
    */
    'defaults' => [
        'label_format' => 'PDF', // PDF or ZPL
        'print_options' => [
            'outputFormat' => 'PDF',
            'paperFormat' => 'A4',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable detailed logging of SOAP requests/responses for debugging
    |
    */
    'logging' => [
        'enabled' => env('DPD_LOGGING_ENABLED', false),
        'channel' => env('DPD_LOGGING_CHANNEL', 'stack'),
    ],
];
