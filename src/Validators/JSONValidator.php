<?php

namespace Stellif\Stellif\Validators;

use Askonomm\Hird\Validators\Validator;

/**
 * Implements a JSON validator that has a job 
 * of validating that a given value is valid
 * form of JSON.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class JSONValidator implements Validator
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
		json_decode($value);

		return json_last_error() === JSON_ERROR_NONE;
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
		return "${field} is not JSON.";
	}
}
