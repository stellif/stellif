<?php

declare(strict_types=1);

namespace Stellif\Stellif;

use Askonomm\Hird\Hird;
use Stellif\Stellif\Validators\DateFormatValidator;
use Stellif\Stellif\Validators\EqualValidator;
use Stellif\Stellif\Validators\JSONValidator;
use Stellif\Stellif\Validators\UniqueValidator;

/**
 * The Request class provides utility methods that are related to the 
 * HTTP request that came from the client (browser, etc), such as 
 * retrieving the current URL of a request, getting POST or GET input, 
 * managing session, validation of inputs, and so forth.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class Request
{
    private array $params = [];

    /**
     * Returns the current URL of the request. Is protocol aware so 
     * that when the current request comes from a HTTPS protocol, will
     * return a URL such as `https://example.com`, and otherwise returns
     * a URL such as `http://example.com`. Does not include request path.
     *
     * @return string
     */
    public function url(): string
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
            'https://' . $_SERVER['HTTP_HOST']
            : 'http://' . $_SERVER['HTTP_HOST'];
    }

    public function param(int|string $identifier): string
    {
        if (isset($this->params[$identifier])) {
            return $this->params[$identifier];
        }

        return '';
    }

    public function setParam(int|string $identifier, string $value): void
    {
        $this->params[$identifier] = $value;
    }

    /**
     * Returns the value of an input item that matches `$key`. Does not 
     * care if the input came from a GET or a POST request. If the 
     * input item with the given `$key` does not exist, will return `null`.
     *
     * @param string $key
     * @return mixed
     */
    public function input(string $key): mixed
    {
        if (isset($this->all()[$key])) {
            return $this->all()[$key];
        }

        return null;
    }

    /**
     * Returns an array containing the entire array of `$_REQUEST`.
     *
     * @return array
     */
    public function all(): array
    {
        $phpinput = json_decode(file_get_contents('php://input'), true);
        $phpinputdata = $phpinput ?? [];

        return [
            ...$_REQUEST,
            ...$phpinputdata,
        ];
    }

    /**
     * Takes in an array of `$validators` that it then initializes
     * a `Validator` class with, using the current requests input 
     * items to match, and returns the instance of that `Validator`.
     *
     * @param array $validators
     * @return Validator
     */
    public function validate(array $rules): Hird
    {
        $hird = new Hird($this->all(), $rules);

        // Custom validators
        $hird->registerValidator('unique', (new UniqueValidator));
        $hird->registerValidator('json', (new JSONValidator));
        $hird->registerValidator('equal', (new EqualValidator));

        return $hird;
    }

    /**
     * Initializes a `Session` class and returns the instance of that 
     * class.
     *
     * @return Session
     */
    public function session(): Session
    {
        return new Session();
    }
}
