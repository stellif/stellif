<?php

declare(strict_types=1);

namespace Stellif\Stellif;

/**
 * The Session class is responsible for utility methods
 * that make it a breeze to work with the system provided
 * `$_SESSION` superglobal variable. 
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class Session
{
    /**
     * Retrieves the value of a session item with the given `$key`.
     * If the session item does not exist, will return `false` by default.
     * The default return value can be overwritten by providing a second, 
     * optional argument to the `get` method.
     *
     * @param string $key
     * @param boolean $default
     * @return mixed
     */
    public function get(string $key, bool $default = false): mixed
    {
        if ($this->has($key)) {
            return $_SESSION[$key];
        }

        return $default;
    }

    /**
     * Returns a boolean `true` if the value of a session item with 
     * the given `$key` exists and is not `null`. Returns `false` otherwise.
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]) && $_SESSION[$key] !== null;
    }

    /**
     * Returns a boolean `true` if the session item with the given `$key` 
     * exists, even if the value of it is `null`. Returns `false` otherwise.
     *
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Returns a boolean `true` if the session item with the given `$key` 
     * does not exist or if the value of it is `null`. Returns `false`
     * otherwise.
     *
     * @param string $key
     * @return boolean
     */
    public function missing(string $key): bool
    {
        return !$this->has($key);
    }

    /**
     * Sets the value of a session item with a given `$key` to `$value`. 
     * If the session item already exists, it will overwrite it.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Unsets the session item with a given `$key`.
     *
     * @param string $key
     * @return void
     */
    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }
}
