<?php

declare(strict_types=1);

namespace Stellif\Stellif;

use Stellif\Stellif\Updater;
use Bramus\Router\Router;
use Askonomm\Siena\Siena;

/**
 * The Core class is responsible for generic app things that
 * don't quite fit anywhere else, such as initializing the app and 
 * checking if the app is installed.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class Core
{
    protected function store(): Siena
    {
        return new Siena(STELLIF_ROOT . '/store');
    }

    /**
     * Starts a session as well as initialises the database 
     * connection. 
     *
     * @return void
     */
    private function init(): void
    {
        session_start();
    }

    /**
     * Returns the name of the public directory.
     *
     * @return string
     */
    public function publicDir(): string
    {
        $dir = 'public';

        if (is_dir(STELLIF_ROOT . '/public_html')) {
            $dir = 'public_html';
        }

        if (is_dir(STELLIF_ROOT . '/htdocs')) {
            $dir = 'htdocs';
        }

        return $dir;
    }

    /**
     * Checks if the database file exists, which is used for 
     * indicating if the app is set-up or not.
     *
     * @return boolean
     */
    public function isSetup(): bool
    {
        return count($this->store()->find('users')->get()) !== 0;
    }

    /**
     * Depending on whether Stellif is set-up or not, 
     * composes an array of routes accordingly.
     *
     * @return array
     */
    private function routes(): array
    {
        if ($this->isSetup()) {
            return [
                ...require STELLIF_ROOT . '/routes/api.php',
                ...require STELLIF_ROOT . '/routes/admin.php',
                ...require STELLIF_ROOT . '/routes/site.php'
            ];
        }

        return require STELLIF_ROOT . '/routes/setup.php';
    }

    /**
     * Get the parameter names from a route pattern, in order, 
     * to be able to match against route arguments in `setRoutes`. 
     *
     * @param string $pattern
     * @return array
     */
    private function getParamNamesFromRoutePattern(string $pattern): array
    {
        $names = [];
        $parts = explode('/', $pattern);

        foreach ($parts as $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $names[] = substr($part, 1, strlen($part) - 2);
            }
        }

        return $names;
    }

    /**
     * Sets `$routes` to the router.
     *
     * @param Router $router
     * @return void
     */
    private function setRoutes(Router $router): void
    {
        foreach ($this->routes() as $route) {
            $router->{$route['method']}($route['pattern'], function ($p0 = null, $p1 = null, $p2 = null, $p3 = null) use ($route) {
                $callableControllerName = explode('@', $route['callable'])[0];
                $callableClassName = "\Stellif\Stellif\Controllers\\${callableControllerName}";
                $callableClassMethod = explode('@', $route['callable'])[1];
                $callableClass = new $callableClassName();
                $request = new Request();
                $paramNames = $this->getParamNamesFromRoutePattern($route['pattern']);

                // Set params
                if ($p0) $request->setParam($paramNames[0], $p0);
                if ($p1) $request->setParam($paramNames[1], $p1);
                if ($p2) $request->setParam($paramNames[2], $p2);
                if ($p3) $request->setParam($paramNames[3], $p3);

                $response = new Response();

                // If there is a beforeCallable run it before the actual callable, and if it
                // returns a truthy value let's not run the actual callable at all.
                if (isset($route['beforeCallable'])) {
                    $beforeCallableControllerName = explode('@', $route['beforeCallable'])[0];
                    $beforeCallableClassName = "\Stellif\Stellif\Controllers\\${beforeCallableControllerName}";
                    $beforeCallableClassMethod = explode('@', $route['beforeCallable'])[1];
                    $beforeCallableClass = new $beforeCallableClassName();

                    if (!call_user_func([$beforeCallableClass, $beforeCallableClassMethod], $request, $response)) {
                        call_user_func([$callableClass, $callableClassMethod], $request, $response);
                    }
                }

                // Otherwise we won't care and the callable will be called normally.
                else {
                    call_user_func([$callableClass, $callableClassMethod], $request, $response);
                }

                // If there is a afterCallable run it after the actual callable.
                if (isset($route['afterCallable'])) {
                    $afterCallableControllerName = explode('@', $route['afterCallable'])[0];
                    $afterCallableClassName = "\Stellif\Stellif\Controllers\\${$afterCallableControllerName}";
                    $afterCallableClassMethod = explode('@', $route['afterCallable'])[1];
                    $afterCallableClass = new $afterCallableClassName();

                    call_user_func([$afterCallableClass, $afterCallableClassMethod], $request, $response);
                }
            });
        }
    }

    /**
     * Runs app.
     *
     * @return void
     */
    public function run(): void
    {
        // Init app
        $this->init();

        // Run updater
        if ($this->isSetup()) {
            (new Updater);
        }

        // Register routes
        $router = new Router();
        $this->setRoutes($router);

        // Run app
        $router->run();
    }
}
