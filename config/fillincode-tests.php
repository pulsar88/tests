<?php

return [
    'feature' => [
        'prefix' => 'web',

        'users' => [
            'guest' => '',
            'user' => 'Passport',
            'web_user' => 'web',
        ],

        'codes' => [
            'guest' => 401,
            'user' => 200,
            'web_user' => 200,
        ],

        'invalid' => [
            'data' => 422,
            'parameters' => 404
        ],
    ],

    'admin_panel' => [
        'name' => 'moonshine',

        'prefix' => 'admin',

        'users' => [
            'guest' => '',
            'admin' => 'moonshine',
        ],

        'codes' => [
            'guest' => 401,
            'admin' => 200,
        ],

        'invalid' => [
            'data' => 422,
            'parameters' => 404
        ],
    ]
];