<?php

class Service
{
	public $observers = array();
	public $methods = array();

	/**
	 * Attach an object or closure observer
	 *
	 * @param mixed $observer
	 * @return object
	 */
	public function attach($observer)
	{
		if($observer instanceof $observer)
		{
			method_exists($observer, 'attached') AND $observer->attached($this);
		}

		$this->observers[] = $observer;

		return $this;
	}

	/**
	 * Remove an object or closure observer
	 *
	 * @param mixed $observer
	 * @return object
	 */
	public function detach($observer)
	{
		if($observer instanceof $observer)
		{
			method_exists($observer, 'detached') AND $observer->detached($this);
		}

		foreach($this->observers as $key => $entity)
		{
			if($entity === $observer)
			{
				unset($this->observers[$key]);
			}
		}

		return $this;
	}

	/**
	 * Alert all observers to an event
	 *
	 * @param string $event name
	 * @param mixed $params to pass to each observer
	 */
	public function event($event, $params = NULL)
	{
		foreach($this->observers as $observer)
		{
			if( ! ($observer instanceof $observer)) // Closure or Object
			{
				// Create object when needed... and then throw away
				$observer = new $observer;
			}

			is_callable($observer) AND $observer($this, $event, $params);
		}
	}

	/**
	 * Attach an object, class name, or closure service
	 *
	 * @param string $key
	 * @param mixed $callable
	 */
	function __set($key, $callable)
	{
		$this->methods[strtolower($key)] = $callable;
	}

	/**
	 * Call an object, class name, or closure service using a factory pattern.
	 *
	 * @param string $key
	 * @param array $args
	 * @return mixed
	 */
	function __call($key, $args)
	{
		if( ! isset($this->methods[$key = strtolower($key)]))
		{
			throw new Exception("$key service not found");
		}

		if(! $args) return $this->methods[$key]($this);

		array_unshift($args, $this);

		return call_user_func_array($this->methods[$key], $args);
	}

	/**
	 * Return an object, class name, or closure service using a singleton pattern.
	 *
	 * @param string $key
	 * @return mixed
	 */
	function __get($key)
	{
		if( ! isset($this->methods[$key = strtolower($key)]))
		{
			throw new Exception("$key service not found");
		}

		if($this->methods[$key] instanceof Closure)
		{
			$this->methods[$key] = $this->methods[$key]($this);
		}

		return $this->methods[$key];
	}

	/**
	 * @see isset
	 */
	function __isset($key)
	{
		return isset($this->methods[strtolower($key)]);
	}

	/**
	 * @see unset
	 */
	function __unset($key)
	{
		unset($this->methods[strtolower($key)]);
	}

	/**
	 * Create a singleton pattern around a closure function
	 *
	 * @param closure $callable
	 * @return object
	 */
	function singleton($callable)
	{
		return function ($service) use ($callable)
		{
			static $object;
			return $object ?: ($object = $callable($service));
		};
	}
}

/**
 * Create a singleton instance of the service object
 *
 * @param boolean $kill service object if TRUE
 * @return object
 */
function s($kill = FALSE)
{
	static $s; if($kill) $s = NULL; return $s ?: ($s = new Service);
}

/**
 * Observer interface
 */
Abstract class Observer
{
	public function attached($service) {}
	public function detached($service) {}
	public function __invoke($service, $event, $params = NULL) {}
}

