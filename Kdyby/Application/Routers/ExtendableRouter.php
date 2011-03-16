<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */



namespace Kdyby\Application\Routers;

use Kdyby\Model AS Model,
	Nette,
	Nette\String,
	Nette\Application\PresenterRequest;


/**

Zdravím,
vymyšlím jak hezky zapisovat odkazy do jednotlivých rozšíření.

Současná logika presenterů mi nevyhovuje,
takže jsem to trochu hodně ohnul a vymyslel ("a z části realizoval":http://github.com/HosipLan/Kdyby) jinou.
U mě probíhá načítá presenterů tak, že se matchne kousek URL, podle toho se vyparsuje pár parametrů,
podle nich se načte v databázi záznam, který mi řekne jak mám popsat zbytek URL, jaké použít rozšíření a presenter, ...
Protože u mě presentery netvoří strom webu, ale "pouze" seskupují pohledy a akce co patří k sobě,
jsou potom volány podle struktury v databázi.

Mám strom presenterů v databázi a tudíž tam můžu mít jeden presenter použitý na více místech na webu a
třeba se bude lišit pouze layoutem. Nebo lepší příklad (ikdyž tohle je jen modelová situace),
budu mít presenter `Clanek` a ten si zavolám, předám mu parametry (vlastně né já ale Nette :) a on si zavolá model,
vytáhne článek nebo nějaký text a zobrazí ho.

 */


/**
 * The bidirectional extendable route is responsible for mapping
 * HTTP request to a PresenterRequest object for dispatch and vice-versa.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
final class ExtendableRouter extends Nette\Object implements Nette\Application\IRouter, \ArrayAccess, \Countable, \IteratorAggregate
{

	/********************* variables for extending *********************/


	/** @var string */
	const DADDY = 'daddy';

	/** @var string */
	const INDEX = 'index';

	/** @var string */
	const ADMIN = 'admin';

	/** @var array */
	protected $routes = array();

	/** @var array */
	protected $cachedRoutes;

	/** @var int */
	protected $routesDefaultCount = 0;


	/** @var string */
	protected $name;

	/** @var string */
	protected $parent;

	/** @var bool */
	protected $strict = True;


	/********************* variables for Router *********************/


//	const PRESENTER_KEY = 'presenter';
	const APP_KEY = 'app';

	/** flag */
	const CASE_SENSITIVE = 256;
	const IS_LAST = 512;

	/**#@+ @internal uri type */
	const HOST = 1;
	const PATH = 2;
	const RELATIVE = 3;
	/**#@-*/

	/**#@+ key used in {@link Route::$styles} or metadata {@link Route::__construct} */
	const VALUE = 'value';
	const PATTERN = 'pattern';
	const FILTER_IN = 'filterIn';
	const FILTER_OUT = 'filterOut';
	const FILTER_TABLE = 'filterTable';
	/**#@-*/

	/**#@+ @internal fixity types - how to handle default value? {@link Route::$metadata} */
	const OPTIONAL = 0;
	const PATH_OPTIONAL = 1;
	const CONSTANT = 2;
	/**#@-*/

	/** @var bool */
	public static $defaultFlags = 0;

	/** @var array */
	public static $styles = array(
		'#' => array( // default style for path parameters
			self::PATTERN => '[^/]+',
			self::FILTER_IN => 'rawurldecode',
			self::FILTER_OUT => 'rawurlencode',
		),
		'?#' => array( // default style for query parameters
		),
		'app' => array(
			self::PATTERN => '[a-z][a-z0-9.-]*',
			self::FILTER_IN => array(__CLASS__, 'path2presenter'),
			self::FILTER_OUT => array(__CLASS__, 'presenter2path'),
		),
//		'presenter' => array(
//			self::PATTERN => '[a-z][a-z0-9.-]*',
//			self::FILTER_IN => array(__CLASS__, 'path2presenter'),
//			self::FILTER_OUT => array(__CLASS__, 'presenter2path'),
//		),
		'action' => array(
			self::PATTERN => '[a-z][a-z0-9-]*',
			self::FILTER_IN => array(__CLASS__, 'path2action'),
			self::FILTER_OUT => array(__CLASS__, 'action2path'),
		),
		'?app' => array(
		),
//		'?presenter' => array(
//		),
		'?action' => array(
		),
	);

	/** @var string */
	protected $mask;

	/** @var array */
	protected $sequence;

	/** @var string  regular expression pattern */
	protected $re;

	/** @var array of [value & fixity, filterIn, filterOut] */
	protected $metadata = array();

	/** @var array  */
	protected $xlat;

	/** @var int HOST, PATH, RELATIVE */
	protected $type;

	/** @var int */
	protected $flags;



	/**
	 * @param string $name
	 * @param string $mask
	 * @param array $metadata
	 * @param int $flags
	 * @param ExtendableRouter $parent 
	 */
	public function __construct($name = self::DADDY, $mask = Null, array $metadata = array(), $flags = 0, ExtendableRouter $parent = Null, $strict = True)
	{
		if( !String::match($name, "#^[a-zA-Z0-9]+$#") ){
			throw new \InvalidArgumentException("Route \$name must be alphanumeric string!");
		}

		if( $parent === Null AND $name !== self::DADDY ){
			throw new \InvalidArgumentException("You cannot name root route!");
		}

		$this->name = $name;
		$this->parent = $parent;
		$this->flags = $flags;
		$this->strict = (bool)$strict;

		if( $parent === Null ){ // children, daddy will now lookup for some cache, ups i mean cake!
			$c = $this->getCache();

			if( isset($c['routes']) ){
				$this->routes = $c['routes'];
				$this->routesDefaultCount = count($this);
			}

		} else {
			$this->flags = $flags | self::$defaultFlags;
			$this->setMask($mask, $metadata);
		}
	}


	/**
	 * @param string $name
	 * @param string $mask
	 * @param array $metadata
	 * @param bool $strict
	 * @throws \InvalidArgumentException
	 * @return ExtendableRouter
	 */
	public function extend($name, $mask, array $metadata = array(), $flags = 0, $strict = Null)
	{
		if( $name === self::DADDY ){
			throw new \InvalidArgumentException("Name '".self::DADDY."' is reserved!");
		}

		if( isset($this[$name]) ){
			throw new \InvalidArgumentException("Name '".$name."' is already taken!");
		}

		if( $strict === Null ){
			$strict = $this->strict;
		}

		return $this[$name] = new self($name, $mask, $metadata, $this->flags | $flags, $this, (bool)$strict);
	}


	public function getName()
	{
		return $this->name;
	}


	/**
	 * Invalidates routes
	 */
	public function invalidateRoutes()
	{
		$this->getCache()->save('routes', $this->routes);
		$this->routesDefaultCount = count($this);
	}


	/**
	 * @return Nette\Caching\Cache
	 */
	protected function getCache()
	{
		return Nette\Environment::getCache("Kdyby.Router");
	}



	/**
	 * Returns default values.
	 * @return array
	 */
	public function getDefaults()
	{
		$defaults = array();
		foreach ($this->metadata as $name => $meta) {
			if (isset($meta['fixity'])) {
				$defaults[$name] = $meta[self::VALUE];
			}
		}
		return $defaults;
	}


	protected function getRe($strict = FALSE /*$isLast = FALSE*/)
	{
		return
		    '#^' . // begin
		    $this->re . '/?' . //($isLast OR ($this->flags & self::IS_LAST) ? '$' : '') . // re
		    ($strict ? '$' : '') .
		    '#A' . ($this->flags & self::CASE_SENSITIVE ? '' : 'iu'); // end
	}


	public function getStrict()
	{
		return $this->strict;
	}



	/********************* matching url *********************/



	/**
	 * Maps HTTP request to a PresenterRequest object.
	 * @param  Nette\Web\IHttpRequest
	 * @return PresenterRequest|NULL
	 */
	public function match(Nette\Web\IHttpRequest $httpRequest)
	{
		$httpQuery = $httpRequest->getQuery();
		$childrenParams = array();

		if( $this->parent === Null ){ // job for daddy
			$routes = $this->routes;

			// daddy wanna know what domain he's banging
			$hostParts = String::match($httpRequest->getUri()->host, "~^(www\.)?(?P<domain>[-A-Z0-9.]+)$~i");

			do{
				if( count($routes) === 0 ){
					return Null;
				}

				$uri = clone $httpRequest->getUri();

				foreach ($routes as $rootRoute => $route) {
					$appRequest = $route->matchLeaf($uri, $httpQuery, (count($this)==0));
					if( $appRequest !== Null ){
						break;

					} else {
						unset($routes[$rootRoute]);
					}
				}

				unset($routes[$rootRoute]); // repeatance is not funny!

				if( $appRequest === NULL OR $rootRoute === self::INDEX ){ // childrens are bored
					$node = Model\Node::getDefault($hostParts['domain'], $rootRoute);

				} else { // seems like daddy's children are having some work to do
					$node = Model\Node::getNode($appRequest, $rootRoute, $hostParts['domain']);
				}

				if( empty($node) ){ // damn! Maybe try another ?
					continue;
				}

				if( $rootRoute === self::ADMIN ){
					return $this->createRequest($httpRequest, $node, $appRequest);

				} elseif( $rootRoute === self::INDEX ){
					if( !String::match($uri->path, $this->getRe(TRUE)) ){
						continue;
					}
				}

				if( empty($appRequest['action']) ){
					$appRequest['action'] = $node['defaultAction'];
				}

				// nice! we have names of every children of children, ... ready to go!
				$node['routeLeafs'] = String::split($node['route'], "#\\~#");

				$cursor = $this[$rootRoute];
				foreach( $iterator = new Nette\SmartCachingIterator($node['routeLeafs']) AS $leaf ){
					if( $leaf != "" ){
						if( isset($cursor[$leaf]) ){
							$cursor = $cursor[$leaf];

							if( $params = $cursor->matchLeaf($uri, $httpQuery, (count($cursor)==0)) ){
								$childrenParams += $params;

							} elseif( $cursor->strict ) {
								break;
							}

						} elseif( $cursor->strict ) {
							break;
						}
					}

					if( $iterator->isLast() ){
						return $this->createRequest($httpRequest, $node, $appRequest + $childrenParams);
					}
				}
			} while(TRUE);
		}

		return Null;
	}



	private function createRequest(Nette\Web\HttpRequest $httpRequest, $node, $params)
	{ _dump($node);
		if( isset($node['presenter']) ){
			return new PresenterRequest( // daddy's sending letter to mommy!
				$node['presenter'], // TODO: FUCK TYPES!
				$httpRequest->getMethod(),
				$params,
				$httpRequest->getPost(),
				$httpRequest->getFiles(),
				array(PresenterRequest::SECURED => $httpRequest->isSecured())
			);
		}
	}



	public function matchLeaf(&$uri, $httpQuery, $isLast)
	{ //_dump(get_defined_vars()); if( $isLast ) die();
		// combine with precedence: mask (params in URL-path), fixity, query, (post,) defaults

		// 1) URL MASK
		if ($this->type === self::HOST) {
			$path = '//' . $uri->getHost() . $uri->getPath();

		} elseif ($this->type === self::RELATIVE) {
			$basePath = $uri->getBasePath();
			if (strncmp($uri->getPath(), $basePath, strlen($basePath)) !== 0) {
				return NULL;
			}
			$path = (string) substr($uri->getPath(), strlen($basePath));

		} else {
			$path = $uri->getPath();
		}

		if ($path !== '') {
			$path = rtrim($path, '/') . '/';
		}

		if (!$matches = String::match($path, $this->getRe(/*$isLast*/))) {
			// stop, not matched
			return NULL;
		}

		$uri->setPath(preg_replace("~^".preg_quote(rtrim($matches[0], '/'))."~i", "", $path));

		// deletes numeric keys, restore '-' chars
		$params = array();
		foreach ($matches as $k => $v) {
			if (is_string($k) && $v !== '') {
				$params[str_replace('___', '-', $k)] = $v; // trick
			}
		}


		// 2) CONSTANT FIXITY
		foreach ($this->metadata as $name => $meta) {
			if (isset($params[$name])) {
				//$params[$name] = $this->flags & self::CASE_SENSITIVE === 0 ? strtolower($params[$name]) : */$params[$name]; // strtolower damages UTF-8

			} elseif (isset($meta['fixity']) && $meta['fixity'] !== self::OPTIONAL) {
				$params[$name] = NULL; // cannot be overwriten in 3) and detected by isset() in 4)
			}
		}


		if( ($this->flags & self::IS_LAST) OR $isLast ){ // 3) QUERY
			if ($this->xlat) {
				$params += self::renameKeys($httpQuery, array_flip($this->xlat));
			} else {
				$params += $httpQuery;
			}
		}


		// 4) APPLY FILTERS & FIXITY
		foreach ($this->metadata as $name => $meta) {
			if (isset($params[$name])) {
				if (!is_scalar($params[$name])) {

				} elseif (isset($meta[self::FILTER_TABLE][$params[$name]])) { // applyies filterTable only to scalar parameters
					$params[$name] = $meta[self::FILTER_TABLE][$params[$name]];

				} elseif (isset($meta[self::FILTER_IN])) { // applyies filterIn only to scalar parameters
					$params[$name] = call_user_func($meta[self::FILTER_IN], (string) $params[$name]);
					if ($params[$name] === NULL && !isset($meta['fixity'])) {
						return NULL; // rejected by filter
					}
				}

			} elseif (isset($meta['fixity'])) {
				$params[$name] = $meta[self::VALUE];
			}
		}


		return $params;
	}



	/**
	 * Parse mask and array of default values; initializes object.
	 * @param  string
	 * @param  array
	 * @return void
	 */
	protected function setMask($mask, array $metadata)
	{
		$this->mask = $mask;

		// detect '//host/path' vs. '/abs. path' vs. 'relative path'
		if (substr($mask, 0, 2) === '//') {
			$this->type = self::HOST;

		} elseif (substr($mask, 0, 1) === '/') {
			$this->type = self::PATH;

		} else {
			$this->type = self::RELATIVE;
		}

		foreach ($metadata as $name => $meta) {
			if (!is_array($meta)) {
				$metadata[$name] = array(self::VALUE => $meta, 'fixity' => self::CONSTANT);

			} elseif (array_key_exists(self::VALUE, $meta)) {
				$metadata[$name]['fixity'] = self::CONSTANT;
			}
		}

		// PARSE MASK
		$parts = String::split($mask, '/<([^># ]+) *([^>#]*)(#?[^>\[\]]*)>|(\[!?|\]|\s*\?.*)/'); // <parameter-name [pattern] [#class]> or [ or ] or ?...

		$this->xlat = array();
		$i = count($parts) - 1;

		// PARSE QUERY PART OF MASK
		if (isset($parts[$i - 1]) && substr(ltrim($parts[$i - 1]), 0, 1) === '?') {
			$matches = String::matchAll($parts[$i - 1], '/(?:([a-zA-Z0-9_.-]+)=)?<([^># ]+) *([^>#]*)(#?[^>]*)>/'); // name=<parameter-name [pattern][#class]>

			foreach ($matches as $match) {
				list(, $param, $name, $pattern, $class) = $match;  // $pattern is not used

				if ($class !== '') {
					if (!isset(self::$styles[$class])) {
						throw new \InvalidStateException("Parameter '$name' has '$class' flag, but Route::\$styles['$class'] is not set.");
					}
					$meta = self::$styles[$class];

				} elseif (isset(self::$styles['?' . $name])) {
					$meta = self::$styles['?' . $name];

				} else {
					$meta = self::$styles['?#'];
				}

				if (isset($metadata[$name])) {
					$meta = $metadata[$name] + $meta;
				}

				if (array_key_exists(self::VALUE, $meta)) {
					$meta['fixity'] = self::OPTIONAL;
				}

				unset($meta['pattern']);
				$meta['filterTable2'] = empty($meta[self::FILTER_TABLE]) ? NULL : array_flip($meta[self::FILTER_TABLE]);

				$metadata[$name] = $meta;
				if ($param !== '') {
					$this->xlat[$name] = $param;
				}
			}
			$i -= 5;
		}

		$brackets = 0; // optional level
		$re = '';
		$sequence = array();
		$autoOptional = array(0, 0); // strlen($re), count($sequence)
		do {
			array_unshift($sequence, $parts[$i]);
			$re = preg_quote($parts[$i], '#') . $re;
			if ($i === 0) break;
			$i--;

			$part = $parts[$i]; // [ or ]
			if ($part === '[' || $part === ']' || $part === '[!') {
				$brackets += $part[0] === '[' ? -1 : 1;
				if ($brackets < 0) {
					throw new \InvalidArgumentException("Unexpected '$part' in mask '$mask'.");
				}
				array_unshift($sequence, $part);
				$re = ($part[0] === '[' ? '(?:' : ')?') . $re;
				$i -= 4;
				continue;
			}

			$class = $parts[$i]; $i--; // validation class
			$pattern = trim($parts[$i]); $i--; // validation condition (as regexp)
			$name = $parts[$i]; $i--; // parameter name
			array_unshift($sequence, $name);

			if ($name[0] === '?') { // "foo" parameter
				$re = '(?:' . preg_quote(substr($name, 1), '#') . '|' . $pattern . ')' . $re;
				$sequence[1] = substr($name, 1) . $sequence[1];
				continue;
			}

			// check name (limitation by regexp)
			if (preg_match('#[^a-z0-9_-]#i', $name)) {
				throw new \InvalidArgumentException("Parameter name must be alphanumeric string due to limitations of PCRE, '$name' given.");
			}

			// pattern, condition & metadata
			if ($class !== '') {
				if (!isset(self::$styles[$class])) {
					throw new \InvalidStateException("Parameter '$name' has '$class' flag, but Route::\$styles['$class'] is not set.");
				}
				$meta = self::$styles[$class];

			} elseif (isset(self::$styles[$name])) {
				$meta = self::$styles[$name];

			} else {
				$meta = self::$styles['#'];
			}

			if (isset($metadata[$name])) {
				$meta = $metadata[$name] + $meta;
			}

			if ($pattern == '' && isset($meta[self::PATTERN])) {
				$pattern = $meta[self::PATTERN];
			}

			$meta['filterTable2'] = empty($meta[self::FILTER_TABLE]) ? NULL : array_flip($meta[self::FILTER_TABLE]);
			if (array_key_exists(self::VALUE, $meta)) {
				if (isset($meta['filterTable2'][$meta[self::VALUE]])) {
					$meta['defOut'] = $meta['filterTable2'][$meta[self::VALUE]];

				} elseif (isset($meta[self::FILTER_OUT])) {
					$meta['defOut'] = call_user_func($meta[self::FILTER_OUT], $meta[self::VALUE]);

				} else {
					$meta['defOut'] = $meta[self::VALUE];
				}
			}
			$meta[self::PATTERN] = "#(?:$pattern)$#A" . ($this->flags & self::CASE_SENSITIVE ? '' : 'iu');

			// include in expression
			$re = '(?P<' . str_replace('-', '___', $name) . '>' . $pattern . ')' . $re; // str_replace is dirty trick to enable '-' in parameter name
			if ($brackets) { // is in brackets?
				if (!isset($meta[self::VALUE])) {
					$meta[self::VALUE] = $meta['defOut'] = NULL;
				}
				$meta['fixity'] = self::PATH_OPTIONAL;

			} elseif (isset($meta['fixity'])) { // auto-optional
				$re = '(?:' . substr_replace($re, ')?', strlen($re) - $autoOptional[0], 0);
				array_splice($sequence, count($sequence) - $autoOptional[1], 0, array(']', ''));
				array_unshift($sequence, '[', '');
				$meta['fixity'] = self::PATH_OPTIONAL;

			} else {
				$autoOptional = array(strlen($re), count($sequence));
			}

			$metadata[$name] = $meta;
		} while (TRUE);

		if ($brackets) {
			throw new \InvalidArgumentException("Missing closing ']' in mask '$mask'.");
		}

		$this->re = $re; // get re using $this->getRe(); !
		$this->metadata = $metadata;
		$this->sequence = $sequence;
	}



	/**
	 * Returns mask.
	 * @return string
	 */
	public function getMask()
	{
		return $this->mask;
	}



	/********************* constructing url *********************/



	/**
	 * Constructs absolute URL from PresenterRequest object.
	 * @param  Nette\Web\IHttpRequest
	 * @param  PresenterRequest
	 * @return string|NULL
	 */
	public function constructUrl(PresenterRequest $appRequest, Nette\Web\IHttpRequest $httpRequest)
	{
		
	}



	/********************* Utilities ****************d*g**/



	/**
	 * Proprietary cache aim.
	 * @return string|FALSE
	 */
	public function getTargetPresenter()
	{
		if ($this->flags & self::ONE_WAY) {
			return FALSE;
		}

		$m = $this->metadata;
		$module = '';

		if (isset($m[self::MODULE_KEY])) {
			if (isset($m[self::MODULE_KEY]['fixity']) && $m[self::MODULE_KEY]['fixity'] === self::CONSTANT) {
				$module = $m[self::MODULE_KEY][self::VALUE] . ':';
			} else {
				return NULL;
			}
		}

		if (isset($m[self::PRESENTER_KEY]['fixity']) && $m[self::PRESENTER_KEY]['fixity'] === self::CONSTANT) {
			return $module . $m[self::PRESENTER_KEY][self::VALUE];
		}
		return NULL;
	}



	/**
	 * Rename keys in array.
	 * @param  array
	 * @param  array
	 * @return array
	 */
	protected static function renameKeys($arr, $xlat)
	{
		if (empty($xlat)) return $arr;

		$res = array();
		$occupied = array_flip($xlat);
		foreach ($arr as $k => $v) {
			if (isset($xlat[$k])) {
				$res[$xlat[$k]] = $v;

			} elseif (!isset($occupied[$k])) {
				$res[$k] = $v;
			}
		}
		return $res;
	}



	/********************* Inflectors ****************d*g**/



	/**
	 * camelCaseAction name -> dash-separated.
	 * @param  string
	 * @return string
	 */
	protected static function action2path($s)
	{
		$s = preg_replace('#(.)(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		$s = rawurlencode($s);
		return $s;
	}



	/**
	 * dash-separated -> camelCaseAction name.
	 * @param  string
	 * @return string
	 */
	protected static function path2action($s)
	{
		$s = strtolower($s);
		$s = preg_replace('#-(?=[a-z])#', ' ', $s);
		$s = substr(ucwords('x' . $s), 1);
		//$s = lcfirst(ucwords($s));
		$s = str_replace(' ', '', $s);
		return $s;
	}



	/**
	 * PascalCase:Presenter name -> dash-and-dot-separated.
	 * @param  string
	 * @return string
	 */
	protected static function presenter2path($s)
	{
		$s = strtr($s, ':', '.');
		$s = preg_replace('#([^.])(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		$s = rawurlencode($s);
		return $s;
	}



	/**
	 * dash-and-dot-separated -> PascalCase:Presenter name.
	 * @param  string
	 * @return string
	 */
	protected static function path2presenter($s)
	{
		$s = strtolower($s);
		$s = preg_replace('#([.-])(?=[a-z])#', '$1 ', $s);
		$s = ucwords($s);
		$s = str_replace('. ', ':', $s);
		$s = str_replace('- ', '', $s);
		return $s;
	}



	/********************* Route::$styles manipulator ****************d*g**/



	/**
	 * Creates new style.
	 * @param  string  style name (#style, urlParameter, ?queryParameter)
	 * @param  string  optional parent style name
	 * @param  void
	 */
	public static function addStyle($style, $parent = '#')
	{
		if (isset(self::$styles[$style])) {
			throw new \InvalidArgumentException("Style '$style' already exists.");
		}

		if ($parent !== NULL) {
			if (!isset(self::$styles[$parent])) {
				throw new \InvalidArgumentException("Parent style '$parent' doesn't exist.");
			}
			self::$styles[$style] = self::$styles[$parent];

		} else {
			self::$styles[$style] = array();
		}
	}



	/**
	 * Changes style property value.
	 * @param  string  style name (#style, urlParameter, ?queryParameter)
	 * @param  string  property name (Route::PATTERN, Route::FILTER_IN, Route::FILTER_OUT, Route::FILTER_TABLE)
	 * @param  mixed   property value
	 * @param  void
	 */
	public static function setStyleProperty($style, $key, $value)
	{
		if (!isset(self::$styles[$style])) {
			throw new \InvalidArgumentException("Style '$style' doesn't exist.");
		}
		self::$styles[$style][$key] = $value;
	}



	/********************* interfaces ArrayAccess, Countable & IteratorAggregate *********************/



	/**
	 * Adds the router.
	 * @param  mixed
	 * @param  IRouter
	 * @return void
	 */
	public function offsetSet($index, $route)
	{
		if (!($route instanceof Nette\Application\IRouter)) {
			throw new \InvalidArgumentException("Argument must be IRouter descendant.");
		}

		if( !String::match($index, "#^[a-zA-Z0-9]+$#") ){
			throw new \InvalidArgumentException("Route \$name must be alphanumeric string!");
		}

		if( $index === self::DADDY ){
			throw new \InvalidArgumentException("Name '".self::DADDY."' is reserved!");
		}

		if( isset($this[$index]) ){
			throw new \NotSupportedException("You cannot overwrite router");
		}

		$this->routes[$index] = $route;
	}



	/**
	 * Returns router specified by index. Throws exception if router doesn't exist.
	 * @param  mixed
	 * @return IRouter
	 */
	public function offsetGet($index)
	{
		return $this->routes[$index];
	}



	/**
	 * Does router specified by index exists?
	 * @param  mixed
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return isset($this->routes[$index]);
	}



	/**
	 * Removes router.
	 * @param  mixed
	 * @return void
	 */
	public function offsetUnset($index)
	{
		throw new \NotSupportedException("You cannot delete router");
	}



	/**
	 * Iterates over routers.
	 * @return \Traversable
	 */
	public function getIterator()
	{
		return $this->routes;
	}



	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->routes);
	}
}
