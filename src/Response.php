<?php

declare(strict_types=1);

namespace Stellif\Stellif;

use Stellif\Stellif\Templating\BlocksParser;
use Twig\Loader\FilesystemLoader as TwigFs;
use Twig\Environment as TwigEnv;
use Twig\TwigFunction as TwigFn;

/**
 * The Response class takes care of things that are
 * related to the HTTP response that the client (browser, etc)
 * will receive, such as redirects, displaying of content, 
 * setting headers, and so forth.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class Response
{
    /**
     * Will attempt to return a compiled template from the `STELLIF_ROOT/views` 
     * directory with the given `$viewName` and `$data`. If the view was not 
     * found, returns nothing (blank page) and writes an error into the log.
     *
     * @param string $viewName
     * @param array $data
     * @return mixed
     */
    public function view(string $viewName, array $data = []): bool
    {
        try {
            // Init loader
            $loader = new TwigFs(STELLIF_ROOT . '/views');

            // Init env
            $twig = new TwigEnv($loader, [
                'cache' => STELLIF_ROOT . '/cache/twig',
                'auto_reload' => true,
            ]);

            // Implement BlocksParser
            $twig->addFunction(new TwigFn('parse_blocks', function ($content) {
                return (new BlocksParser($content))->parse();
            }));

            // Load template
            $template = $twig->load($viewName . '.twig');

            // Render
            echo $template->render($data);

            return true;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Sets the HTTP headers according to `$headers`. The key of each item 
     * in this array will be used as the header name and the value of each
     * item in this array as the value of said header. 
     * 
     * An example use:
     * 
     * ```php
     * $response = new Response();
     * 
     * $response->headers([
     *  'Content-Type' => 'application/json'
     * ]);
     * ```
     *
     * @param array $headers
     * @return void
     */
    public function headers(array $headers = []): void
    {
        foreach ($headers as $k => $v) {
            header($k . ': ' . $v);
        }
    }

    /**
     * Returns the given array of `$data` in a JSON encoded form. If the given 
     * `$data` is somehow malfromed in a way that cannot be JSON encoded, it 
     * will return an empty JSON array instead, and write the error to the log.
     *
     * @param array $data
     * @return mixed
     */
    public function json(array|object $data = []): bool
    {
        $this->headers([
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
        ]);

        try {
            echo json_encode($data);
        } catch (\Exception $e) {
            Logger::log(__METHOD__, $e->getMessage());
            echo json_encode([]);
        }

        return true;
    }

    /**
     * Redirects the client to the given `$path`.
     *
     * @param string $path
     * @return void
     */
    public function redirect(string $path): bool
    {
        $this->headers([
            'Location' => $path
        ]);

        return true;
    }

    /**
     * Sends given `$contents` to the client. Useful if wanting to 
     * return a string. Optionally takes in a second argument `$headers`
     * that allows you conveniently set the headers.
     */
    public function make(string $contents, $headers = []): bool
    {
        $this->headers($headers);

        echo $contents;

        return true;
    }
}
