<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Kdyby\DependencyInjection;

use Kdyby;
use Nette;
use Nette\Environment;
use Nette\Config\Config;



/**
 * ServiceContainer builder
 *
 * @author	Patrik Votoček
 * @author	David Grudl
 *
 * @property-write string $ServiceContainerClass
 * @property-read IServiceContainer $ServiceContainer
 */
class ServiceContainerBuilder extends Nette\Object implements IServiceContainerBuilder
{

	/** @var string */
	private $serviceContainerClass = 'Kdyby\DependencyInjection\ServiceContainer';

	/** @var array */
	private $autoRunServices = array();



	/**
	 * @param string
	 * @return ServiceContainerBuilder
	 * @throws \InvalidArgumentException
	 */
	public function setServiceContainerClass($class)
	{
		if (!class_exists($class)) {
			throw new \InvalidArgumentException("ServiceContainer class '$class' does not exist");
		}

		$ref = new Nette\Reflection\ClassReflection($class);
		if (!$ref->implementsInterface('Kdyby\DependencyInjection\IServiceContainer')) {
			throw new \InvalidArgumentException("ServiceContainer class '$class' is not valid 'Kdyby\DependencyInjection\IServiceContainer'");
		}

		$this->serviceContainerClass = $class;
		return $this;
	}



	/**
	 * @return IServiceContainer
	 */
	public function getServiceContainer()
	{
		return Environment::getContext();
	}



	/**
	 * @param string
	 */
	protected function loadEnvironmentName($name)
	{
		Environment::setVariable('environment', $name);
		$this->getServiceContainer()->setEnvironment($name);
	}



	/**
	 * @param Nette\Config\Config
	 * @throws \NotSupportedException
	 */
	protected function loadIni(Config $config)
	{
		if (PATH_SEPARATOR !== ';' && isset($config->include_path)) {
			$config->include_path = str_replace(';', PATH_SEPARATOR, $config->include_path);
		}

		foreach (clone $config as $key => $value) { // flatten INI dots
			if ($value instanceof Config) {
				unset($config->$key);
				foreach ($value as $k => $v) {
					$config->{"$key.$k"} = $v;
				}
			}
		}

		foreach ($config as $key => $value) {
			if (!is_scalar($value)) {
				throw new \InvalidStateException("Configuration value for directive '$key' is not scalar.");
			}

			self::iniSet($key, $value);
		}
	}



	/**
	 * @param string $key
	 * @param mixed $value
	 */
	protected static function iniSet($key, $value)
	{
		if ($key === 'date.timezone') { // PHP bug #47466
			date_default_timezone_set($value);
		}

		if (function_exists('ini_set')) {
			ini_set($key, $value);

		} else {
			switch ($key) {
				case 'include_path':
					set_include_path($value);
					break;

				case 'iconv.internal_encoding':
					iconv_set_encoding('internal_encoding', $value);
					break;

				case 'mbstring.internal_encoding':
					mb_internal_encoding($value);
					break;

				case 'date.timezone':
					date_default_timezone_set($value);
					break;

				case 'error_reporting':
					error_reporting($value);
					break;

				case 'ignore_user_abort':
					ignore_user_abort($value);
					break;

				case 'max_execution_time':
					set_time_limit($value);
					break;

				default:
					if (ini_get($key) != $value) { // intentionally ==
						throw new \NotSupportedException('Required function ini_set() is disabled.');
					}
			}
		}
	}



	/**
	 * @param Nette\Config\Config $config
	 */
	protected function loadParameters(Config $config)
	{
		$serviceContainer = $this->getServiceContainer();

		foreach ($config as $key => $value) {
			if (in_array($key, array('variable', 'variables')) && $value instanceof Config) {
				foreach ($value as $k => $v) {
					$serviceContainer->setParameter($k, $v);
					Environment::setVariable($k, $v);
				}

			} elseif ($key != "php" && !in_array($key, array('service', 'services'))) {
				$tmp = $value instanceof Config ? $value->toArray() : $value;
				$serviceContainer->setParameter($key, $tmp);
			}
		}

		$serviceContainer->expandParameters();
	}



	/**
	 * @param array
	 */
	protected function loadServices(array $config)
	{
		foreach ($config as $name => $data) {
			$this->fallbackAddService($this->getServiceContainer(), $name, $data);
		}
	}



	/**
	 * @param IServiceContainer $serviceContainer
	 * @param string $name
	 * @param array $data
	 */
	protected function fallbackAddService(IServiceContainer $serviceContainer, $name, $data)
	{
		if (is_string($data)) {
			$serviceContainer->addService($name, $data);

		} else {
			$service = key_exists('class', $data) ? $data['class'] : (key_exists('factory', $data) ? $data['factory'] : NULL);

			$serviceContainer->addService($name, $service, key_exists('singleton', $data) ? $data['singleton'] : TRUE, $data);

			if (key_exists('run', $data) && $data['run']) {
				$this->autoRunServices[] = $name;
			}
		}
	}



	/**
	 * @param Nette\Config\Config $config
	 */
	protected function loadConstants(Config $config)
	{
		foreach ($config as $key => $value) {
			define($key, $value);
		}
	}



	/**
	 * @param Nette\Config\Config $config
	 */
	protected function loadModes(Config $config)
	{
		foreach($config as $mode => $state) {
			Environment::setMode($mode, $state);
		}
	}



	/**
	 * @return void
	 */
	protected function autoRunServices()
	{
		foreach ($this->autoRunServices as $serviceName) {
			$this->getServiceContainer()->getService($serviceName);
		}

		$this->autoRunServices = array();
	}



	/**
	 * Loads global configuration from file and process it.
	 * @param  Nette\Config\Config  file name or Config object
	 * @return Nette\Config\Config
	 *
	 * @author Patrik Votoček
	 */
	public function loadConfig(Config $config)
	{
		$environment = Environment::getName(); // BACK compatability
		$this->loadEnvironmentName($environment);

		$this->loadParameters($config);
		isset($config->php) && $this->loadIni($config->php);
		isset($config->service) && $this->loadServices($config->service->toArray());
		isset($config->services) && $this->loadServices($config->services->toArray());
		isset($config->const) && $this->loadConstants($config->const);
		isset($config->mode) && $this->loadModes($config->mode);

		$this->autoRunServices();

		return $config;
	}



	/**
	 * Get initial instance of ServiceContainer
	 *
	 * @return Kdyby\DependencyInjection\IServiceContainer
	 */
	public function createServiceContainer()
	{
		$serviceContainer = new $this->serviceContainerClass;
		foreach (DefaultServiceFactories::$defaultServices as $name => $data) {
			$this->fallbackAddService($serviceContainer, $name, $data);
		}

		return $serviceContainer;
	}

}
