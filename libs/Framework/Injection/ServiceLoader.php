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
class ServiceLoader extends Nette\Object
{

	const PREFIX_SERVICE = '%';
	const PREFIX_VAR_ENV = 'E$';
	const PREFIX_VAR_CONF = 'C$';

	/** @var Kdyby\Injection\IServiceContainer */
	private $container;



	/**
	 * @param Kdyby\Injection\IServiceContainer $container
	 */
	public function __construct(IServiceContainer $container)
	{
		$this->container = $container;
	}



	/**
	 * @return Kdyby\Injection\IServiceContainer
	 */
	public function getContainer()
	{
		return $this->container;
	}



	/**
	 * @param string $serviceName
	 * @param string|array $configuration
	 * @return Kdyby\Injection\ServiceLoader
	 */
	public function addService($serviceName, $configuration)
	{
		$definition = is_string($configuration) ? array('class' => $configuration) : (array)$configuration;
		$singleton = isset($configuration['singleton']) ? (bool)$configuration['singleton'] : TRUE;

		$this->getContainer()->addService($serviceName, array($this, 'serviceFactory'), $singleton, $definition);

		return $this;
	}



	/**
	 * @author http://github.com/janmarek
	 *
	 * @param array $options
	 * @return Kdyby\Injection\IService
	 */
	public function serviceFactory(array $definition)
	{
		extract($definition + array_fill_keys(array('class', 'factory', 'autowire', 'arguments', 'callMethods'), NULL));

		if ($class) {
			$object = $this->createInstanceOfService($class, $arguments ?: array(), $autowire);
		}

		// object factory
		if ($factory) {
			if ($autowire) {
				throw new \NotImplementedException("Autowiring for factories is not implemented.");
			}

			$arguments = $this->processArguments($arguments);
			$object = call_user_func_array($factory, $arguments);
		}

		// method injection
		if ($callMethods) {
			foreach ($callMethods as $method => $args) {
				call_user_func_array(array($object, $method), $this->processArguments($args));
			}
		}

		return $object;
	}



	/**
	 * @param string $class
	 * @param array $options
	 * @param bool $autowire
	 * @return Kdyby\Injection\IService
	 */
	public function createInstanceOfService($class, array $arguments = array(), $autowire = TRUE)
	{
		$classReflection = Kdyby\Reflection\ServiceReflection::from($class);

		if ($autowire) {
			$classes = $classReflection->getConstructorParamClasses();

			if ($classes) {
				$arguments = array_map(function ($name) {
						return ServiceLoader::PREFIX_SERVICE . $name;
				}, $classes);
			}
		}

		$arguments = $this->processArguments($arguments);

		$instance = $arguments ? $classReflection->newInstanceArgs($arguments) : new $class();
		$instance->setContainer($this->getContainer());

		return $instance;
	}



	// todo: callAndAutowireFactory



	/**
	 * @param array $arguments
	 * @param Kdyby\Injection\IServiceContainer $container
	 * @param array|Nette\Config\Config $config
	 * @return array
	 */
	public function processArguments(array $arguments)
	{
		$container = $this->getContainer();

		return array_map(function ($arg) use ($container) {
			if (!is_string($arg)) { // what else could it be?
				return $arg;
			}

			// %service
			// %service%service%service%se...
			if (substr($arg, 0, strlen(ServiceLoader::PREFIX_SERVICE)) === ServiceLoader::PREFIX_SERVICE) {
				$service = $container;

				$prl = strlen(ServiceLoader::PREFIX_SERVICE);
				do {
					$nextServiceIndex = stripos($arg, ServiceLoader::PREFIX_SERVICE, $prl);
					$end = ($nextServiceIndex ?: strlen($arg))-$start;
					$serviceName = substr($arg, $prl, $end);

					$service = $container->getService($serviceName);
					$arg = substr($arg, stripos($arg, ServiceLoader::PREFIX_SERVICE, $prl) ?: strlen($arg));
				} while (substr($arg, 0, $prl) === ServiceLoader::PREFIX_SERVICE);

				return $service;
			}

			// C$variable
			if (substr($arg, 0, strlen(ServiceLoader::PREFIX_VAR_CONF)) === ServiceLoader::PREFIX_VAR_CONF) {
				return \Nette\Environment::getConfig(substr($arg, strlen(ServiceLoader::PREFIX_VAR_CONF)));
			}

			// E$variable
			if (substr($arg, 0, strlen(ServiceLoader::PREFIX_VAR_ENV)) === ServiceLoader::PREFIX_VAR_ENV) {
				return \Nette\Environment::getVariable(substr($arg, strlen(ServiceLoader::PREFIX_VAR_ENV)));
			}

			return $arg;
		}, $arguments);
	}

}