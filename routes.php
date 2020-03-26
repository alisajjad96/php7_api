<?php
return [
    'welcome' => [
        'class' => \PHP7API\Components\Welcome::class,
        'method' => 'welcome'
    ],
    'welcome_args' => [
        'class' => \PHP7API\Components\Welcome::class,
        'method' => 'welcomeArgs',
        'requires' => [
            'arg_1',
            'arg_2',
        ]
    ],
];
