<?php

declare(strict_types=1);

namespace Stellif\Stellif\Controllers;

use Stellif\Stellif\Core;
use Stellif\Stellif\Request;
use Stellif\Stellif\Response;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * The SetupController is the first entry-point of a user to the
 * application and directs the user through the installation and  
 * set-up of the application.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class SetupController
{
    /**
     * Displays the set-up page.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function index(Request $request, Response $response)
    {
        return $response->view('stellif/setup');
    }

    /**
     * Validates input, creates a database, the user, and then
     * redirects to the /admin route.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function action(Request $request, Response $response)
    {
        // Validate request
        $validator = $request->validate([
            'email' => 'required|email',
            'password' => 'required|len:8'
        ]);

        if ($validator->fails()) {
            return $response->view('stellif/setup', [
                'error' => $validator->firstError()
            ]);
        }

        // Create database
        $this->createDatabase();

        // Create user
        Capsule::table('users')->insert([
            'email' => $request->input('email'),
            'password' => password_hash($request->input('password'), PASSWORD_BCRYPT),
        ]);

        // Wait for DB creation.
        usleep(500000);

        return $response->redirect('/admin');
    }

    /**
     * Creates the database file and schema.
     *
     * @return void
     */
    private function createDatabase()
    {
        // Create database file
        file_put_contents(Core::$dbPath, '');

        // Create users table
        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        // Create meta table
        Capsule::schema()->create('meta', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });

        // Create posts table
        Capsule::schema()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->text('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('status');
            $table->text('content')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });

        // Create post meta table
        Capsule::schema()->create('post_meta', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });
    }
}
