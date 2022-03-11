<?php

declare(strict_types=1);

namespace Stellif\Stellif\Controllers;

use Stellif\Stellif\Core;
use Stellif\Stellif\Request;
use Stellif\Stellif\Response;
use Askonomm\Siena\Siena;

class SiteController extends Core
{
    private function getTheme(): string
    {
        $meta = $this->store()->find('meta')->where(['_id' => 'site'])->first();

        return $meta && isset($meta['theme']) ? $meta['theme'] : 'default';
    }

    public function index(Request $request, Response $response)
    {
        $meta = $this->store()->find('meta')->where(['_id' => 'site'])->first();
        $posts = $this->store()->find('posts')
            ->where(['status' => 'published'])
            ->orderAsc('published_at')
            ->get();

        return $response->view('themes/' . $this->getTheme() . '/home', [
            'url' => $request->url(),
            'posts' => $posts,
            ...$meta,
        ]);
    }

    public function post(Request $request, Response $response)
    {
        $meta = $this->store()->find('meta')->where(['_id' => 'site'])->first();
        $post = $this->store()->find('posts')->where(['slug|_id' => $request->param('identifier')])->first();

        if ($post) {
            return $response->view('themes/' . $this->getTheme() . '/post', [
                'url' => $request->url(),
                'post' => $post,
                ...$meta,
            ]);
        }

        return $response->make('Post not found ...');
    }
}
