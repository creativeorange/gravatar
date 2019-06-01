<?php namespace Creativeorange\Gravatar;

use Creativeorange\Gravatar\Exceptions\InvalidEmailException;
use Illuminate\Support\Arr;

/**
 * Class Gravatar
 * @package Creativeorange\Gravatar
 */
class Gravatar {

	/**
	 * Gravatar base url
	 *
	 * @var string
	 */
	private $publicBaseUrl = 'https://www.gravatar.com/avatar/';

	/**
	 * Gravatar secure base url
	 *
	 * @var string
	 */
	private $secureBaseUrl = 'https://secure.gravatar.com/avatar/';

	/**
	 * Email address to check
	 *
	 * @var string
	 */
	private $email;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var string
	 */
	private $fallback;

	/**
	 * Override the default image fallback set in the config.
	 * Can either be a public URL to an image or a valid themed image.
	 * For more info, visit http://en.gravatar.com/site/implement/images/#default-image
	 *
	 * @param string $fallback
	 * @return $this
	 */
	public function fallback ($fallback)
	{

		if (
			filter_var($fallback, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)
			||
			in_array( $fallback, array('mm', 'identicon', 'monsterid', 'wavatar', 'retro', 'blank') )
		)
			$this->fallback = $fallback;

		else
			$this->fallback = false;

		return $this;
	}

	/**
	 * Check if Gravatar has an avatar for the given email address
	 *
	 * @param $email
	 * @return bool
	 * @throws InvalidEmailException
	 */
	public function exists ($email)
	{
		$this->checkEmail($email);
		$this->email = $email;

		$this->setConfig(['fallback' => 404]);

		$headers = @get_headers($this->buildUrl());

		return (boolean) strpos($headers[0], '200');
	}

	/**
	 * Get the gravatar url
	 *
	 * @param $email
	 * @param string $configGroup
	 * @return string
	 * @throws InvalidEmailException
	 */
	public function get($email, $configGroup = 'default')
	{
		$this->checkEmail($email);

		$this->setConfig($configGroup);
		$this->email = $email;

		return $this->buildUrl();
	}

	/**
	 * Helper function for setting the config based on either:
	 * 1. The name of a config group
	 * 2. A custom array
	 * 3. The default group in the config
	 *
	 * @param string|array|null $group
	 * @return $this
	 */
	private function setConfig ($group = null)
	{
		if (is_string($group) && $group != 'default')
			$this->config = Arr::dot( array_replace_recursive( config('gravatar.default'), config('gravatar.'.$group) ) );
		elseif (is_array($group))
			$this->config = Arr::dot( array_replace_recursive( config('gravatar.default'), $group) );
		else
			$this->config = Arr::dot( config('gravatar.default') );

		return $this;
	}

	/**
	 * Helper function to retrieve config settings.
	 *
	 * @param $value
	 * @param null $default
	 * @return null
	 */
	protected function c ($value, $default = null)
	{
		return array_key_exists($value, $this->config) ? $this->config[$value] : $default;
	}

	/**
	 * Helper function to md5 hash the email address
	 *
	 * @return string
	 */
	private function hashEmail()
	{
		return md5( strtolower( trim( $this->email ) ) );
	}

	/**
	 * @return string
	 */
	private function getExtension ()
	{
		$v = $this->c('forceExtension');

		return $v ? '.' . $v : '';
	}

	/**
	 * @return string
	 */
	private function buildUrl()
	{
		$url  = $this->c('secure') === true ? $this->secureBaseUrl : $this->publicBaseUrl;
		$url .= $this->hashEmail();
		$url .= $this->getExtension();
		$url .= $this->getUrlParameters();

		return $url;
	}

	/**
	 * @return string
	 */
	private function getUrlParameters ()
	{
		$build = array ();

		foreach ( get_class_methods($this) as $method)
		{
			if ( substr($method, -strlen('Parameter')) !== 'Parameter')
				continue;

			if ($called = call_user_func(array($this, $method)))
				$build = array_replace($build, $called);
		}

		return '?'.http_build_query($build);
	}

	/**
	 * @return array|null
	 */
	private function sizeParameter ()
	{
		if ( ! $this->c('size') || ! is_integer($this->c('size')) )
			return null;

		return array('s' => $this->c('size'));
	}

	/**
	 * @return array|null
	 */
	private function defaultParameter ()
	{
		$this->fallback = $this->c( 'fallback' );

		if ( ! $this->fallback )
			return null;

		return array( 'd' => $this->fallback );
	}

	/**
	 * @return array|null
	 */
	private function ratingParameter ()
	{
		$rating = $this->c('maximumRating');

		if ( ! $rating || ! in_array($rating, array('g','pg','r','x') ) )
			return null;

		return array('r' => $rating);
	}

	/**
	 * @return array|null
	 */
	private function forceDefaultParameter ()
	{
		if ($this->c('forceDefault') === true)
			return array('forcedefault' => 'y');

		return null;
	}

	/**
	 * Check if the provided email address is valid
	 *
	 * @param $email
	 * @throws InvalidEmailException
	 */
	private function checkEmail($email)
	{
		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL))
			throw new InvalidEmailException ('Please specify a valid email address');
	}
}
