<?php

namespace Stellif\Stellif\Validators;

use Askonomm\Hird\Validators\Validator;

/**
 * Implements a Equal validator that has a job 
 * of validating that a given value is equal to 
 * the validation requirements.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class EqualValidator implements Validator
{
    /**
     * Returns a boolean `true` when given `$value` is a valid e-mail
     * address. Returns `false` otherwise.
     *
     * @param string $field
     * @param mixed $value
     * @param mixed $modifier
     * @return boolean
     */
    public static function validate(string $field, mixed $value, mixed $modifier = null): bool
    {
        $opts = explode(',', $modifier);

        return in_array($value, $opts);
    }

    /**
     * Composes the error message in case the validation fails.
     *
     * @param string $field
     * @param mixed $modifier
     * @return string
     */
    public static function composeError(string $field, mixed $modifier = null): string
    {
        return "${field} is does not match ${modifier}.";
    }
}
