<?php

return [
    'app' => [
        'default' => [
            'users' => [
                'guest' => '',
                'api_user' => 'Passport',
                'web_user' => 'web',
            ],

            'codes' => [
                'valid' => [
                    'guest' => 401,
                    'user' => 200,
                    'web_user' => 200,
                ],

                'invalid' => [
                    'data' => 422,
                    'parameters' => 404
                ]
            ],
        ],

        'api' => [
            'invalid' => [
                'data' => 404,
                'parameters' => 401
            ]
        ],

        'web' => [
            'invalid' => [
                'data' => 404,
                'parameters' => 401
            ]
        ]
    ],

    'admin_panel' => [
        'name' => 'moonshine',

        'users' => [
            'guest' => '',
            'admin' => 'moonshine',
        ],

        'codes' => [
            'valid' => [
                'guest' => 401,
                'admin' => 200,
            ],

            'invalid' => [
                'invalid_validate' => 422,
                'invalid_parameters' => 404
            ],
        ],
    ]
];
