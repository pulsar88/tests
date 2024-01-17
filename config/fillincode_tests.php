<?php

return [
    'users' => [
        'guest',
        'user' => 'Passport',
        'admin' => 'web',
    ],

    'user' => 200,
    'admin' => 200,
    'guest' => 401,

    'invalid_data' => 422,
    'invalid_parameters' => 404,
];