<?php

return [
    [
        'pattern' => '/',
        'method' => 'get',
        'callable' => 'SiteController@index',
    ],
    [
        'pattern' => '/{identifier}',
        'method' => 'get',
        'callable' => 'SiteController@post',
    ],
];
