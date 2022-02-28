<?php

return [
    [
        'pattern' => '/admin.*',
        'method' => 'get',
        'callable' => 'AdminController@index'
    ],
];
