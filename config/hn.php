<?php

return [
    'default' => env('HN_DATA_SOURCE', 'html'),

    'html' => [
        'class' => App\HN\HTMLFetcher::class
    ],

    'api' => [
        'class' => App\HN\APIFetcher::class
    ],
];
