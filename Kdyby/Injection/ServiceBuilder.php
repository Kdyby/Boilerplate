<?php

namespace Kdyby\Injection;

use Nette;
use Kdyby;



/**
 * @author Honza Marek
 * @author Filip Procházka
 * @see https://github.com/janmarek
 * @see https://gist.github.com/04fd914f3fcbb2112d3f#file_service_loader.php
 */
class ServiceBuilder extends Nette\Object
{

	const PREFIX_SERVICE = '§';
	const PREFIX_VAR_ENV = 'E$';
	const PREFIX_VAR_CONF = 'C$';

	/** @var Kdyby\Injection\ServiceContainer */
	private $container;



	/**
	 * @param Kdyby\Injection\ServiceContainer $container
	 */
	public function __construct(ServiceContainer $container)
	{
		$this->container = $container;
		$this->container->setServiceBuilder($this);
	}



	/**
	 * @param callable $service
	 * @param array|Description $options
	 * @return Description
	 */
	public function createDescription($service, array $options)
	{
		$descriptor = isset($options['descriptor']) ? $options['descriptor'] : NULL;

		if (!$descriptor || !$descriptor instanceof Description) {
			$descriptor = new Description($service);
		}

		unset($options['descriptor']);
		$descriptor->setArguments((array)$options);

		return $descriptor;
	}



	/**
	 * @author http://github.com/janmarek
	 *
	 * @param array $options
	 * @return object
	 */
	public function serviceFactory(array $definition)
	{
		$description = $definition['description'];

		// class autowire
		if ($description->isCreatorClass()) {
			if ($description->autowire && $description->arguments) {
				throw new \InvalidArgumentException("Can't use autowire, when given arguments.");
			}

			$arguments = $description->autowire && !$description->arguments ? array() : $description->arguments;
			$object = $this->createInstanceOfService($description->creator, $description->arguments ?: array(), $description->autowire);
		}

		// object factory
		if ($description->isCreatorFactory()) {
			$object = call_user_func($description->creator, $this->processArguments($description->arguments));
		}

		// method injection
		foreach ($description->methodCalls as $call) {
			call_user_func_array(array($object, $call[0]), $this->processArguments($call[1]));
		}

		// property injection
		foreach ($description->properties as $set) {
			// reflection ??
			// $propReflection = Nette\Reflection\ClassReflection::from($object)->getProperty($set[0]);

			$object->{$set[0]} = current($this->processArguments(array($set[1])));
		}

		return $object;
	}



	/**
	 * @param string $class
	 * @param array $options
	 * @param bool $autowire
	 * @return object
	 */
	public function createInstanceOfService($class, array $arguments = array(), $autowire = TRUE)
	{
		$classReflection = Kdyby\Reflection\ServiceReflection::from($class);

		if ($autowire) {
			$classes = $classReflection->getConstructorParamClasses();

			if ($classes) {
				$arguments = array_map(function ($name) {
						return ServiceBuilder::PREFIX_SERVICE . $name;
				}, $classes);
			}
		}

		$arguments = $this->processArguments($arguments);
		return $arguments ? $classReflection->newInstanceArgs($arguments) : new $class();
	}



	// todo: callAndAutowireFactory



	/**
	 * For passing Context or ServiceContainer into service, requires registered recursion
	 *
	 * @param array $arguments
	 * @return array
	 */
	public function processArguments(array $arguments)
	{
		$container = $this->container;

		return array_map(function ($arg) use ($container) {
			if (!is_string($arg)) { // what else could it be?
				return $arg;
			}

			// %service
			// %service%service%service%se...
			if (substr($arg, 0, strlen(ServiceBuilder::PREFIX_SERVICE)) === ServiceBuilder::PREFIX_SERVICE) {
				$service = $container;

				$serviceNames = Nette\String::split($arg, '~' . preg_quote(ServiceBuilder::PREFIX_SERVICE) . '~', PREG_SPLIT_NO_EMPTY);
				if (count($serviceNames) === 1) {
					if (in_array(current($serviceNames), array('Nette\IContext', 'Kdyby\Injection\IServiceContainer'))) {
						return $service;
					}
				}

				foreach ($serviceNames as $serviceName) {
					$service = $service->getService($serviceName);
				}

				return $service;
			}

			// C$variable
			if (substr($arg, 0, strlen(ServiceBuilder::PREFIX_VAR_CONF)) === ServiceBuilder::PREFIX_VAR_CONF) {
				return \Nette\Environment::getConfig(substr($arg, strlen(ServiceBuilder::PREFIX_VAR_CONF)));
			}

			// E$variable
			if (substr($arg, 0, strlen(ServiceBuilder::PREFIX_VAR_ENV)) === ServiceBuilder::PREFIX_VAR_ENV) {
				return \Nette\Environment::getVariable(substr($arg, strlen(ServiceBuilder::PREFIX_VAR_ENV)));
			}

			return $arg;
		}, $arguments);
	}

}