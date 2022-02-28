<?php

namespace Stellif\Stellif\Validators;

use Askonomm\Hird\Validators\Validator;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Implements a Unique validator that has a job 
 * of validating that a given value in a specified
 * column is unique in a given table.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class UniqueValidator implements Validator
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
		if (Capsule::table($modifier)->where($field, $value)->first()) {
			return false;
		}

		return true;
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
		return "${field} is not unique.";
	}
}
