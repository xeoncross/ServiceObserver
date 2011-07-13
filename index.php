<?php

require('service.php');

Class MyObserver extends Observer
{
	public function __construct()
	{
		print dump(get_class($this) . ' loaded!');
	}

	public function attached($service)
	{
		print dump(get_class($this) . ' was attached.');
	}

	public function detached($service)
	{
		print dump(get_class($this) . ' was detached.');
	}

	public function __invoke($service, $event, $params = NULL)
	{
		print dump(get_class($this) . ' found '. $event);
	}
}


function benchmark($text = '')
{
	static $t,$m;
	if(!$t){$t=microtime(TRUE);$m=memory_get_usage();return;}
	if($text)print dump($text);
	print dump('Memory: '.(memory_get_usage()-$m)."\n".(microtime(TRUE)-$t));
}

function dump()
{
	$string = '';
	foreach(func_get_args() as $value)
	{
		$string .= '<pre>' . ($value === NULL ? 'NULL' : (is_scalar($value) ? $value : print_r($value, TRUE))) . "</pre>\n";
	}
	return $string;
}

benchmark('Starting');

/*
 * First we create a object, class, and closure to test the observer
 * events system.
 */

class A extends MyObserver {}
class B extends MyObserver {}

$b = new B();

$c = function($service, $event, $params = NULL)
{
	print dump('C found '. $event);
};


// Add a class name (to be created each event), an object (to be reused), and a closure
s()->attach('A');
s()->attach($b);
s()->attach($c);

s()->event('event.one', array('params'));

// Remove everything
s()->detach('A');
s()->detach($b);
s()->detach($c);

s()->event('event.two', array('params'));

// Destroy objects to free memory
s(TRUE);
unset($b, $c);


/*
 * Next we will create a singleton class example (a DB connection) and show
 * how using __call() and __get effect the service item.
 */
class DB extends MyObserver
{
	public function __construct()
	{
		print dump('New DB object created!');
	}

	public function query($sql = NULL)
	{
		print dump('Running Query');

		s()->event('db.query');
	}
}

// The closure creates a new database when called
s()->db = function() { return new DB(); };

// Calling __call creates a new database each time (!) because it runs the closure
s()->db()->query();
s()->db()->query();

// Calling __get creates a singleton from the closure and uses it from now on
s()->db->query();
s()->db->query();
s()->db->query();
s()->db->query();

// This no longer works because __get already converted it to a singleton!!!
//s()->db()->query();

benchmark('Done');
