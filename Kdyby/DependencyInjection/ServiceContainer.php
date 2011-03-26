<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Kdyby\DependencyInjection;

use Nette;
use Nette\Reflection\ClassReflection;
use Nette\Environment;



/**
 * Dependency Injection Service Container
 *
 * Aliases are accesible throught class properties, which breaks nette properties
 * Properties are accesible throught ArrayAccess interface
 *
 * @author	Patrik Votoček
 * @author	Filip Procházka
 *
 * @property string $environment
 *
 * @property-read Kdyby\Application\Application $application
 * @property-read Nette\Web\HttpContext $httpContext
 * @property-read Nette\Web\HttpRequest $httpRequest
 * @property-read Nette\Web\HttpResponse $httpResponse
 * @property-read Nette\Mail\IMailer $mailer
 * @property-read Nette\Web\Session $sessionStorage
 * @property-read Nette\Caching\Cache $cache
 * @property-read Kdyby\Security\Authenticator $authenticator
 * @property-read Kdyby\Security\Authorizator $authorizator
 * @property-read Kdyby\Security\User $user
 * @property-read Nette\Caching\ICacheStorage $memcache
 * @property-read Doctrine\ORM\EntityManager $entityManager
 * @property-read Kdyby\Tools\FreezableArray $namespacePrefixes
 * @property-read Kdyby\Tools\FreezableArray $templateDirs
 * @property-read Kdyby\Templates\TemplateFactory $templateFactory
 * @property-read Kdyby\Application\INavigationManager $navigationManager
 */
class ServiceContainer extends Nette\FreezableObject implements IServiceContainer, \ArrayAccess
{

	/** @var array */
	public $onBeforeFreeze = array();

	/** @var string */
	private $environment;

	/** @var array */
	private $parameters = array();

	/** @var array */
	private $aliases = array();

	/** @var array */
	private $registry = array();

	/** @var array */
	private $globalRegistry = array();

	/** @var array */
	private $factories = array();

	/** @var array */
	private $tags = array();



	/**
	 * @return string
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}



	/**
	 * @param string
	 * @return ServiceContainer
	 * @throws \InvalidStateException
	 */
	public function setEnvironment($environment)
	{
		if ($this->isFrozen() && $environment != $this->environment) {
			throw new \InvalidStateException("Service container is frozen for changes");
		}

		$this->environment = $environment;
		return $this;
	}



	/**
	 * @param string
	 * @param mixed
	 * @return ServiceContainer
	 * @throws \InvalidStateException
	 * @throws \InvalidArgumentException
	 */
	public function setParameter($key, $value)
	{
		if ($this->isFrozen()) {
			throw new \InvalidStateException("Service container is frozen for changes");
		}

		if (!is_string($key)) {
			throw new \InvalidArgumentException("Parameter key must be integer or string, " . gettype($key) . " given.");

		} elseif (!preg_match('#^[a-zA-Z0-9_.]+$#', $key)) {
			throw new \InvalidArgumentException("Parameter key must be non-empty alphanumeric string, '$key' given.");
		}

		$this->parameters[$key] = $value;
		return $this;
	}



	/**
	 * @param string
	 * @return mixed
	 */
	public function hasParameter($key)
	{
		if (key_exists($key, $this->parameters)) {
			return TRUE;
		}

		$const = strtoupper(preg_replace('#(.)([A-Z]+)#', '$1_$2', $key));
		$list = get_defined_constants(TRUE);
		if (key_exists('user' , $list) && key_exists($const, $list['user'])) {
			$this->parameters[$key] = $list['user'][$const];
			return TRUE;
		}

		return FALSE;
	}



	/**
	 * @param string
	 * @return mixed
	 * @throws \InvalidStateException
	 */
	public function getParameter($key)
	{
		if (!$this->hasParameter($key)) {
			throw new \InvalidStateException("Unknown Service container parameter '$key'.");
		}

		return $this->expandParameter($this->parameters[$key]);
	}



	/**
	 * @internal
	 * @param array $data
	 * @return array
	 */
	public function expandParameters($data = NULL)
	{
		array_walk_recursive($data, function (&$value, $key, $serviceContainer) {
			$value = $serviceContainer->expandParameter($value);
		}, $this);

		return $data;
	}



	/**
	 * @internal
	 * @param mixed $data
	 * @return mixed
	 */
	public function expandParameter($data = NULL)
	{
		if (is_string($data)) {
			if (substr($data, 0, 1) == '@') {
				if ($this->hasService(substr($data, 1))) {
					$data = $this->getService(substr($data, 1));
				}

			} elseif (strpos($data, '%') !== FALSE) {
				if (substr($data, 0, 1) === '%' && substr($data, -1) === '%' && substr_count($data, '%') === 2) {
					$resolved = $this->resolveParameterFragment(substr($data, 1, -1));
					return is_array($resolved) ? $this->expandParameters($resolved) : $this->expandParameter($resolved);
				}

				foreach (Nette\String::matchAll($data, '~(?P<pattern>\%(?P<varName>[^%]+)\%)~i') as $match) {
					$data = str_replace($match['pattern'], $this->resolveParameterFragment($match['varName']), $data);
				}
			}
		}

		return Environment::expand($data);
	}



	/**
	 * @param string $varName
	 * @return string|NULL
	 */
	private function resolveParameterFragment($varName)
	{
		if ($this->hasParameter($varName)) {
			return $this->getParameter($varName);

		} elseif (!$this->hasParameter($varName) && strpos($varName, '[') !== FALSE) {
			// allows to get variables through "%group[key]%"
			$groupSubKey = Nette\String::match($varName, '~^(?P<group>[^\[]+)(\[(?P<key>[^\[]+)\])?$~i');
			if (!isset($groupSubKey['key']) || empty($groupSubKey['key'])) {
				throw new \InvalidArgumentException("Can't expand given parameter's name '" . $varName . "', right format is '%group[key]%'.");
			}

			try {
				$group = $groupSubKey['group'];
				$key = $groupSubKey['key'];
				return isset($this[$group][$key]) ? $this[$group][$key] : NULL;
			} catch (\InvalidStateException $e) {  }
		}

		return NULL;
	}



	/**
	 * @param string $tag
	 * @param string $service
	 * @return ServiceContainer
	 */
	public function addTag($tag, $service)
	{
		if ($this->isFrozen()) {
			throw new \InvalidStateException("Service container is frozen for changes");
		}

		if (!is_string($tag) || $tag === '') {
			throw new \InvalidArgumentException("Service tag name must be a non-empty string, " . gettype($tag) . " given.");
		}
		if (!is_string($service) || $service === '') {
			throw new \InvalidArgumentException("Service name must be a non-empty string, " . gettype($service) . " given.");
		}

		$lower = strtolower($service);

		if (!isset($this->registry[$lower]) && !isset($this->factories[$lower])) {
			throw new \InvalidArgumentException("Service '$service' not found.");
		}

		$lowerT = strtolower($tag);
		if (!in_array($lower, $this->tag[$lowerT], TRUE)) {
			$this->tags[$lowerT][] = $service;
		}

		return $this;
	}



	/**
	 * @param string $tag
	 * @return array
	 */
	public function getServicesByTag($tag)
	{
		$lowerT = strtolower($tag);

		if (!isset($this->tags[$lowerT])) {
			throw new \InvalidArgumentException("Tag '$tag' not found.");
		}

		$services = array();
		foreach ($this->tags[$lowerT] as $serviceName) {
			$services[] = $this->getService($serviceName);
		}

		return $services;
	}



	/**
	 * Adds the specified service to the service container
	 *
	 * @param string
	 * @param mixed  object, class name or factory callback
	 * @param bool
	 * @param array
	 * @return ServiceContainer
	 * @throws InvalidArgumentException
	 * @throws Nette\AmbiguousServiceException
	 *
	 * @author Patrik Votoček
	 * @author David Grudl
	 */
	public function addService($name, $service, $singleton = TRUE, array $options = NULL)
	{
		if ($this->isFrozen()) {
			throw new \InvalidStateException("Service container is frozen for changes");
		}

		if (!is_string($name) || $name === '') {
			throw new \InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
		}

		$lower = strtolower($name);
		if (isset($this->registry[$lower])) { // only for instantiated services?
			throw new Nette\AmbiguousServiceException("Service named '$name' has already been registered.");
		}

		if ($service instanceof self) {
			$this->registry[$lower] = & $service->registry[$lower];
			$this->factories[$lower] = & $service->factories[$lower];

		} elseif (is_object($service) && !($service instanceof \Closure || $service instanceof Nette\Callback)) {
			if (!$singleton || $options) {
				throw new \InvalidArgumentException("Service named '$name' is an instantiated object and therefore it must be singleton without options.");
			}
			$this->registry[$lower] = $service;

		} else {
			if (!$service) {
				throw new \InvalidArgumentException("Service named '$name' is empty.");
			}

			$factory = new ServiceFactory($this, $name);
			$factory->singleton = $singleton;

			// BACK COMPATIBILITY
			if (is_string($service) && strpos($service, '::') !== FALSE) {
				$service = callback($service);
			}

			if ($service instanceof \Closure || is_callable($service) || $service instanceof Nette\Callback) {
				$factory->factory = $service;

			} else {
				$factory->class = $service;
			}

			if (isset($options['class'])) {
				if (!is_string($options['class'])) {
					throw new \InvalidArgumentException("In options, class key should be allways string, " . gettype($options['class']) . " given as class of service " . $name . ".");
				}

				$factory->class = $options['class'];
			}

			if (isset($options['factory'])) {
				$factory->factory = $options['factory'];
			}

			if (isset($options['arguments'])) {
				$factory->arguments = $options['arguments'];
			}

			if (isset($options['methods'])) {
				$factory->methods = $options['methods'];
			}

			$this->addFactory($factory);

			if (isset($options['aliases'])) {
				foreach ($options['aliases'] as $alias) {
					$this->addAlias($alias, $factory->name);
				}
			}

			if (isset($options['tags'])) {
				foreach ($options['tags'] as $tag) {
					$this->addTag($tag, $factory->name);
				}
			}
		}

		return $this;
	}



	/**
	 * Add service alias
	 *
	 * @param string
	 * @param string
	 * @return ServiceContainer
	 * @throws InvalidArgumentException
	 * @throws Nette\AmbiguousServiceException
	 */
	public function addAlias($alias, $service)
	{
		if ($this->isFrozen()) {
			throw new \InvalidStateException("Service container is frozen for changes");
		}

		if (!is_string($alias) || $alias === '') {
			throw new \InvalidArgumentException("Service alias name must be a non-empty string, " . gettype($alias) . " given.");
		}
		if (!is_string($service) || $service === '') {
			throw new \InvalidArgumentException("Service name must be a non-empty string, " . gettype($service) . " given.");
		}

		$lower = strtolower($service);

		if (!isset($this->registry[$lower]) && !isset($this->factories[$lower])) {
			throw new \InvalidArgumentException("Service '$service' not found.");
		}

		$lowerA = strtolower($alias);
		if (isset($this->aliases[$lowerA]) && $this->aliases[$lowerA] !== $lower) {
			throw new Nette\AmbiguousServiceException("Service alias named '$alias' has already been registered.");
		}

		$this->aliases[$lowerA] = $lower;

		return $this;
	}



	/**
	 * Gets the service object of the specified type
	 *
	 * @param string
	 * @param array
	 * @return mixed
	 * @throws InvalidArgumentException
	 * @throws Nette\AmbiguousServiceException
	 * @throws InvalidStateException
	 *
	 * @author Patrik Votoček
	 * @author David Grudl
	 */
	public function getService($name, array $options = NULL)
	{
		$lower = strtolower($name);

		if (isset($this->registry[$lower])) { // instantiated singleton
			if ($options) {
				throw new \InvalidArgumentException("Service named '$name' is singleton and therefore can not have options.");
			}

			return $this->registry[$lower];

		} elseif (isset($this->factories[$lower])) {
			$factory = $this->getFactory($name, $options ?: array());

			$service = $factory->getInstance();

			if ($factory->singleton) {
				$this->registry[$lower] = $service;
				unset($this->factories[$lower]);
			}

			return $service;
		}

		throw new \InvalidStateException("Service '$name' not found.");
	}



	/**
	 * Exists the service?
	 *
	 * @param  string
	 * @param  bool
	 * @return bool
	 * @throws InvalidArgumentException
	 *
	 * @author Patrik Votoček
	 * @author David Grudl
	 */
	public function hasService($name, $created = FALSE)
	{
		$lower = strtolower($name);
		return isset($this->registry[$lower]) || (!$created && isset($this->factories[$lower]));
	}



	/**
	 * Removes the specified service type from the service container
	 *
	 * @param string
	 * @return ServiceContainer
	 * @throws InvalidArgumentException
	 *
	 * @author Patrik Votoček
	 * @author David Grudl
	 */
	public function removeService($name)
	{
		if ($this->isFrozen()) {
			throw new \InvalidStateException("Service container is frozen for changes");
		}

		if (!is_string($name) || $name === '') {
			throw new \InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
		}

		$lower = strtolower($name);
		unset($this->registry[$lower], $this->factories[$lower]);

		return $this;
	}



	/**
	 * @param string
	 * @return IServiceFactory
	 */
	public function getFactory($name, array $options = array())
	{
		$lower = strtolower($name);
		if (!isset($this->factories[$lower])) {
			throw new \InvalidStateException("Service factory '$name' not found.");
		}

		$factory = $this->factories[$lower];

		if ($options && $factory->singleton) { // todo: ORLY?
			$factory = clone $factory;
		}

		if (isset($options['arguments'])) {
			$factory->arguments = $options['arguments'];
		}

		if (isset($options['methods'])) {
			$factory->methods = $options['methods'];
		}

		return $factory;
	}



	/**
	 * @param IServiceFactory
	 * @return ServiceContainer
	 */
	public function addFactory(IServiceFactory $factory)
	{
		if ($this->isFrozen()) {
			throw new \InvalidStateException("Service container is frozen for changes");
		}

		$lower = strtolower($factory->getName());
		if (isset($this->registry[$lower])) { // only for instantiated services?
			throw new Nette\AmbiguousServiceException("Service named '{$factory->getName()}' has already been registered.");
		}

		$this->factories[$lower] = $factory;
		$this->registry[$lower] = & $this->globalRegistry[$lower]; // forces cloning using reference

		return $this;
	}



	/**
	 * Makes the object unmodifiable.
	 * @return void
	 */
	public function freeze()
	{
		if (!$this->isFrozen()) {
			foreach ($this->parameters as $key => &$value) {
				if (is_array($value)) {

					array_walk_recursive($value, function (&$value, $key, $serviceContainer) {
						$value = $serviceContainer->expandParameter($value);
					}, $this);

				} else {
					$this->parameters[$key] = $this->expandParameter($value);
				}
			}
		}

		parent::freeze();
	}



	/**
	 * Returns property value. Do not call directly.
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws \MemberAccessException if the property is not defined.
	 */
	public function &__get($name)
	{
		$lowerA = strtolower($name);
		if (!isset($this->aliases[$lowerA])) {
			throw new \InvalidArgumentException("Alias " . $name . " is not defined.");
		}

		$service = $this->getService($this->aliases[$lowerA]);
		return $service;
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 * @throws \NotSupportedException
	 */
	public function __set($name, $value)
	{
		throw new \NotSupportedException("For setting aliases use method " . get_class($this) . "::setAlias()");
	}



	/**
	 * Is alias defined?
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->aliases[$name]);
	}



	/**
	 * @param string $name
	 * @throws NotSupportedException
	 */
	public function __unset($name)
	{
		throw new \NotSupportedException("Unsetting aliases is not supported.");
	}



	/**
	 * Tests Service Container parameter presence
	 *
	 * @param string
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		if (!is_string($offset) || $offset === "") {
			throw new \InvalidArgumentException("Parameter name of Service Continar must be non-empty string.");
		}

		return $this->hasParameter($offset);
	}



	/**
	 * Getter for Service Container parameters
	 *
	 * @param string
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		if (!is_string($offset) || $offset === "") {
			throw new \InvalidArgumentException("Parameter name of Service Continar must be non-empty string.");
		}

		return $this->getParameter($offset);
	}



	/**
	 * Setter for Service Container parameters
	 *
	 * @param string $offset
	 * @param mixed $value
	 * @return mixed
	 */
	public function offsetSet($offset, $value)
	{
		if (!is_string($offset) || $offset === "") {
			throw new \InvalidArgumentException("Parameter name of Service Continar must be non-empty string.");
		}

		$this->setParameter($offset, $value);
		return $value;
	}



	/**
	 * @param string $offset
	 * @throws NotSupportedException
	 */
	public function offsetUnset($offset)
	{
		throw new \NotSupportedException("Unsetting of Service Container parameters is not supported.");
	}

}