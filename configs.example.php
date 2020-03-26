<?php
return [
    'db' => [
        'host' => '',
        'user' => '',
        'password' => '',
        'db' => '',
        'port' => 3306,
        'socket' => ''
    ],
    'environment' => [
        'debug' => true,
        'url' => '',
    ],
    'auth' => [
        'type' => 'none',
        /**
         * TYPES:
         *      - None
         *          No Authorization
         *          e.g
         *              [
         *                  'type' => 'None'
         *              ]
         *      - Basic
         *          Requires username & password
         *          e.g
         *              [
         *                  'type' => 'Basic',
         *                  'username' => 'hello',
         *                  'password' => 'world',
         *              ]
         *      - Bearer
         *          Requires token
         *          e.g
         *              [
         *                  'type' => 'Bearer',
         *                  'token' => 'hash_token'
         *              ]
         *      - Digest
         *          Requires username, password and realm
         *          e.g
         *              [
         *                  'type' => 'Digest',
         *                  'username' => 'hello',
         *                  'password' => 'world',
         *                  'realm' => 'host',
         *              ]
         *      - Header
         *          Requires key, value
         *          e.g
         *              [
         *                  'type' => 'Header',
         *                  'key' => 'hello',
         *                  'value' => 'world'
         *              ]
         */
    ]
];
