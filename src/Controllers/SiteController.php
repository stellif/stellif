<?php

declare(strict_types=1);

namespace Stellif\Stellif\Controllers;

use Stellif\Stellif\Request;
use Stellif\Stellif\Response;
use Stellif\Stellif\Store;

class SiteController
{
    private function getTheme(): string
    {
        return Store::getInItem('meta', 'theme', 'default');
    }

    public function index(Request $request, Response $response)
    {
        $posts = Store::get('posts', ['status' => 'published']);

        return $response->view('themes/' . $this->getTheme() . '/home', [
            'url' => $request->url(),
            'posts' => $posts,
        ]);
    }

    public function post(Request $request, Response $response)
    {
        $post = Store::findFirst('posts', ['slug|_id' => $request->param('identifier')]);

        if ($post) {
            return $response->view('themes/' . $this->getTheme() . '/post', [
                'url' => $request->url(),
                'post' => $post,
            ]);
        }

        return $response->make('Post not found ...');
    }
}
