<?php

declare(strict_types=1);

namespace Stellif\Stellif\Controllers;

use Stellif\Stellif\Request;
use Stellif\Stellif\Response;
use Illuminate\Database\Capsule\Manager as Capsule;

class SiteController
{
    private function getTheme(): string
    {
        $theme = Capsule::table('meta')->where('key', 'theme')->first();

        if ($theme) {
            return $theme->value;
        }

        return 'default';
    }

    public function index(Request $request, Response $response)
    {
        $posts = Capsule::table('posts')
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->get();

        return $response->view('themes/' . $this->getTheme() . '/home', [
            'url' => $request->url(),
            'posts' => $posts,
        ]);
    }

    public function post(Request $request, Response $response)
    {
        $post = Capsule::table('posts')
            ->where('status', 'published')
            ->where('slug', $request->param('identifier'))
            ->orWhere('id', $request->param('identifier'))
            ->first();

        return $response->view('themes/' . $this->getTheme() . '/post', [
            'url' => $request->url(),
            'post' => $post,
        ]);
    }
}
