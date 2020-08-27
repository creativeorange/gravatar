<?php

namespace Creativeorange\Gravatar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Creativeorange\Gravatar\Gravatar fallback(string $fallback)
 * @method static string get(string $email, string|array|null $configGroup = 'default')
 * @method static bool exists(string $email)
 *
 * @see \Creativeorange\Gravatar\Gravatar
 */
class Gravatar extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
    {
        return 'gravatar';
    }
}
