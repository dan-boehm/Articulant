<?php

return [
    'autocasting' => [
        'arrays' => 'array',
        'json' => 'array',

        'formats' => [
            'datetime' => DATE_ATOM,
            'date' => 'Y-m-d',
            'time' => 'H:i:s',
        ],
    ],
];