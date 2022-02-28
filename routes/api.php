<?php

return [
    [
        'pattern' => '/api/.*',
        'method' => 'options',
        'callable' => 'APIController@preflight',
    ],
    [
        'pattern' => '/api/authenticate',
        'method' => 'post',
        'callable' => 'APIController@authenticate',
    ],
    [
        'pattern' => '/api/posts',
        'method' => 'get',
        'beforeCallable' => 'APIController@tokenCheck',
        'callable' => 'APIController@getPosts',
    ],
    [
        'pattern' => '/api/post',
        'method' => 'post',
        'beforeCallable' => 'APIController@tokenCheck',
        'callable' => 'APIController@createPost',
    ],
    [
        'pattern' => '/api/post/{id}',
        'method' => 'get',
        'beforeCallable' => 'APIController@tokenCheck',
        'callable' => 'APIController@getPost',
    ],
    [
        'pattern' => '/api/post/{id}',
        'method' => 'post',
        'beforeCallable' => 'APIController@tokenCheck',
        'callable' => 'APIController@updatePost',
    ],
    [
        'pattern' => '/api/post/{id}',
        'method' => 'delete',
        'beforeCallable' => 'APIController@tokenCheck',
        'callable' => 'APIController@deletePost',
    ],
];
