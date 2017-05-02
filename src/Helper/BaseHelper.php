<?php

namespace Friendica\Directory\Helper;

use \OutOfBoundsException;
use \ReflectionMethod;

/**
 * Provides easy access to common helpers, to add them to your views.
 */
abstract class BaseHelper
{
	/**
	 * A reference to the global App.
	 * @var \App
	 */
	protected $app;

	public static function get($name)
	{
		$helper = new static();
		return $helper->{$name};
	}

	public function __construct()
	{
		global $a;
		$this->app = $a;
	}

	//Provides access to a wrapper for your helper functions.
	public function __get($name)
	{
		if (!method_exists($this, $name)) {
			throw new OutOfBoundsException("Helper method '$name' does not exist on " . get_class($this));
		}

		$helper = $this;
		$method = new ReflectionMethod($this, $name);
		return function()use($method, $helper) {
			$arguments = func_get_args();
			return $method->invokeArgs($helper, $arguments);
		};
	}
}
