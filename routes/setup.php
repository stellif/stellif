<?php

return [
    [
        'pattern' => '/.*',
        'method' => 'get',
        'callable' => 'SetupController@index',
    ],
    [
        'pattern' => '/.*',
        'method' => 'post',
        'callable' => 'SetupController@action',
    ],
];
