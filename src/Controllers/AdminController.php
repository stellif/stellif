<?php

declare(strict_types=1);

namespace Stellif\Stellif\Controllers;

use Stellif\Stellif\Request;
use Stellif\Stellif\Response;

/**
 * The AdminController takes care of the server-side routes of 
 * the admin panel, and the authentication of a user.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class AdminController
{
    /**
     * Loads the admin panel.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function index(Request $request, Response $response)
    {
        $admin = file_get_contents(STELLIF_ROOT . '/public/assets/stellif/admin/index.html', true);

        if (!$admin) {
            return $response->make('Could not load admin.');
        }

        $api_url = $request->url() . '/api';
        $inject_api_url_needle = '<stellif>';
        $inject_api_url_html = "<script>window.stellif_api_url = '${api_url}';</script>";

        return $response->make(str_replace($inject_api_url_needle, $inject_api_url_html, $admin));
    }
}
