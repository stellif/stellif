<?php

declare(strict_types=1);

namespace Stellif\Stellif\Controllers;

use Stellif\Stellif\Core;
use Stellif\Stellif\Request;
use Stellif\Stellif\Response;

/**
 * The SetupController is the first entry-point of a user to the
 * application and directs the user through the installation and  
 * set-up of the application.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class SetupController extends Core
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

        $this->store()->put('users/:id', [
            'email' => $request->input('email'),
            'password' => password_hash($request->input('password'), PASSWORD_BCRYPT),
        ]);

        return $response->redirect('/admin');
    }
}
