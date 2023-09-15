<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'sanctum',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token"
    |
    */

    'guards' => [
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'token' => [
            'driver' => 'sanctum',
            'provider' => 'tokens',
        ],
        'address' => [
            'driver' => 'session',
            'provider' => 'addresses',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => FluxErp\Models\User::class,
        ],
        'addresses' => [
            'driver' => 'eloquent',
            'model' => FluxErp\Models\Address::class,
        ],
        'tokens' => [
            'driver' => 'eloquent',
            'model' => FluxErp\Models\Token::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
        ],

        'employers' => [
            'provider' => 'users_employer',
            'expire' => 15,
        ],

    ],
];
