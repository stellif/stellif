<?php

declare(strict_types=1);

namespace Stellif\Stellif\Controllers;

use Stellif\Stellif\Request;
use Stellif\Stellif\Response;
use Stellif\Stellif\Logger;
use Illuminate\Database\Capsule\Manager as Capsule;

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
        $token = $request->input('token');

        // Does the token exist in meta?
        $meta = Capsule::table('meta')->where('value', $token)->first();

        if (!$meta) {
            return $response->json([
                'error' => 'Authentication failed.',
                'errorCode' => 0
            ]);
        }

        // Does the user exist?
        $userId = last(explode('_', $meta->key));
        $user = Capsule::table('users')->where('id', $userId)->first();

        if (!$user) {
            return $response->json([
                'error' => 'Authentication failed.',
                'errorCode' => 1
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
        $user = Capsule::table('users')->where('email', $request->input('email'))->first();

        if (!$user) {
            return $response->json([
                'error' => 'An account with the provided e-mail does not exist.',
                'errorCode' => 2,
            ]);
        }

        if (!password_verify($request->input('password'), $user->password)) {
            return $response->json([
                'error' => 'Password is incorrect.',
                'errorCode' => 3,
            ]);
        }

        // Create and set token
        $token = bin2hex(random_bytes(20));

        Capsule::table('meta')->upsert([[
            'key' => 'auth_token_' . $user->id,
            'value' => $token,
        ]], ['key']);

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
        return $response->json(Capsule::table('posts')->get());
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
        $post = Capsule::table('posts')->where('id', $request->param('id'))->first();

        if (!$post) {
            return $response->json([
                'error' => 'No post found with this ID.',
                'errorCode' => 4
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
        $id = Capsule::table('posts')->insertGetId([
            'user_id' => $request->session()->get('userId'),
            'status' => 'draft',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
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
        Capsule::table('posts')->delete($request->param('id'));

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
        if (!Capsule::table('posts')->where('id', $request->param('id'))->first()) {
            return $response->json([
                'error' => 'Not such post found.',
                'errorCode' => 7,
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
                'errorCode' => 5
            ]);
        }

        // Attempt to update post
        try {
            Capsule::table('posts')->where('id', $request->param('id'))->update([
                'title' => $request->input('title'),
                'slug' => $request->input('slug'),
                'status' => $request->input('status'),
                'content' => $request->input('content'),
                'published_at' => $request->input('published_at'),
            ]);

            return $response->json([
                'ok' => true,
            ]);
        } catch (\Exception $e) {
            Logger::log(__METHOD__, $e->getMessage());

            return $response->json([
                'error' => $e->getMessage(),
                'errorCode' => 6
            ]);
        }
    }
}
