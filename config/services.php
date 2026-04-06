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

    'mfa' => [
        'enabled' => env( 'MFA_ENABLED' ),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'os' => [
        'api_key' => env( 'ONE_SIGNAL_API_KEY' ),
        'app_id' => env( 'ONE_SIGNAL_APP_ID' ),
    ],

    'url' => [
        'admin' => env( 'ADMIN_URL' ),
        'admin_path' => env( 'ADMIN_PATH' ),
        'api' => env( 'API_URL' ),
        'crm' => env( 'CRM_URL' ),
    ],

    'app' => [
        'name' => env( 'APP_NAME' ),
    ],

    'mail' => [
        'receiver' => env( 'MAIL_RECEIVER' )
    ],

    'brevo' => [
        'api_key' => env( 'BREVO_API_KEY' ),
    ],

    'ipay88' => [
        'merchant_code' => env( 'IPAY88_MERCHANT_CODE' ),
        'merchant_key' => env( 'IPAY88_MERCHANT_KEY' ),
        'payment_url' => env( 'IPAY88_PAYMENT_URL', 'https://payment.ipay88.com.my/epayment/entry.asp' ),
        'requery_url' => env( 'IPAY88_REQUERY_URL', 'https://payment.ipay88.com.my/epayment/enquiry.asp' ),
        'response_url' => env( 'IPAY88_RESPONSE_URL' ),
        'backend_url' => env( 'IPAY88_BACKEND_URL' ),
        'currency' => env( 'IPAY88_CURRENCY', 'MYR' ),
    ],
    
    'turnstile' => [
        'request_url' => env( 'TURNSTILE_REQUEST_URL' ),
        'api_id' => env( 'TURNSTILE_APIID' ),
        'api_key' => env( 'TURNSTILE_APIKEY' ),
    ]
];
