<?php

declare(strict_types=1);

namespace Stellif\Stellif\Controllers;

use Stellif\Stellif\Request;
use Stellif\Stellif\Response;
use Stellif\Stellif\Logger;
use Stellif\Stellif\Store;

/**
 * The APIController is responsible for everything you see
 * in the admin panel. Since the admin panel is largely driven 
 * by JS, it relies on an API to function.
 */
class APIController
{
    /**
     * A preflight response to browsers requiring one.
     * 
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function preflight(Request $request, Response $response)
    {
        $response->headers([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT, DELETE',
        ]);
    }

    /**
     * Middleware that runs before any authentication requiring callable
     * that return an error if the authentication token is invalid.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function tokenCheck(Request $request, Response $response)
    {
        // Does the user with such a token exist?
        $user = Store::findFirst('users', ['token' => $request->input('token')]);

        if (!$user) {
            return $response->json([
                'error' => 'Authentication failed.',
            ]);
        }
    }

    /**
     * Authenticates the user.
     * 
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function authenticate(Request $request, Response $response)
    {
        // Preliminary validation of e-mail and password
        $validator = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $response->json([
                'error' => $validator->firstError(),
            ]);
        }

        // Validate user and password 
        $user = Store::findFirst('users', ['email' => $request->input('email')]);

        if (!$user) {
            return $response->json([
                'error' => 'An account with the provided e-mail does not exist.',
            ]);
        }

        if (!password_verify($request->input('password'), $user['password'])) {
            return $response->json([
                'error' => 'Password is incorrect.',
            ]);
        }

        // Create and set token
        $token = bin2hex(random_bytes(20));

        Store::put('users/' . $user['_id'], [
            ...$user,
            'token' => $token,
        ]);

        // All good,
        return $response->json([
            'token' => $token
        ]);
    }

    /**
     * Returns all posts.
     * 
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function getPosts(Request $request, Response $response)
    {
        return $response->json(Store::find('posts'));
    }


    /**
     * Returns a single post.
     * 
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function getPost(Request $request, Response $response)
    {
        $post = Store::findFirst('posts', ['_id' => $request->param('id')]);

        if (!$post) {
            return $response->json([
                'error' => 'No post found with this ID.',
            ]);
        }

        return $response->json($post);
    }

    /**
     * Creates a post and returns its id.
     * 
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function createPost(Request $request, Response $response)
    {
        $id = Store::put('posts/:id', [
            'status' => 'draft',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        return $response->json([
            'id' => $id
        ]);
    }

    /**
     * Deletes a post.
     * 
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function deletePost(Request $request, Response $response)
    {
        Store::remove('posts', ['_id' => $request->param('id')]);

        return $response->json([
            'ok' => true,
        ]);
    }

    /**
     * Updates a post.
     * 
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function updatePost(Request $request, Response $response)
    {
        // Check that the post exists
        if (!Store::findFirst('posts', ['_id' => $request->param('id')])) {
            return $response->json([
                'error' => 'Not such post found.',
            ]);
        }

        // Do preliminary validation
        $validator = $request->validate([
            'status' => 'equal:draft,published',
            'content' => 'json',
            'published_at' => 'date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return $response->json([
                'error' => $validator->firstError(),
            ]);
        }

        // Update post
        Store::update('posts/' . $request->param('id'), [
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'status' => $request->input('status'),
            'content' => $request->input('content'),
            'published_at' => $request->input('published_at') ? strtotime($request->input('published_at')) : null,
        ]);

        return $response->json([
            'ok' => true,
        ]);
    }
}
