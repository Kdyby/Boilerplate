<?php

namespace Kdyby\Environment;

use Kdyby;
use Nette;



class Configurator extends Nette\Configurator
{

	/** @var string */
	private static $kdybyConfigFile = "%kdybyDir%/config.kdyby.neon";

	/** @var string */
	public $defaultConfigFile = '%appDir%/config.neon';

	/** @var array */
	private $configFiles = array();

	/** @var Kdyby\DependencyInjection\IServiceContainerBuilder */
	private $serviceContainerBuilder;



	public function __construct()
	{
		$this->serviceContainerBuilder = new Kdyby\DependencyInjection\ServiceContainerBuilder();

		foreach (array(self::$kdybyConfigFile, $this->defaultConfigFile) as $file) {
			$file = realpath(Nette\Environment::expand($file));
			if (file_exists($file)) {
				$this->configFiles[$file] = array($file, TRUE, array());
			}
		}

		Kdyby\Templates\KdybyMacros::register();
	}



	/**
	 * @param Kdyby\DependencyInjection\IServiceContainerBuilder $builder
	 */
	public function setServiceContainerBuilder(Kdyby\DependencyInjection\IServiceContainerBuilder $builder)
	{
		return $this->serviceContainerBuilder = $builder;
	}



	/**
	 * @return Kdyby\DependencyInjection\IServiceContainerBuilder
	 */
	public function getServiceContainerBuilder()
	{
		return $this->serviceContainerBuilder;
	}



	/**
	 * Detect environment mode.
	 *
	 * @param  string mode name
	 * @return bool
	 */
	public function detect($name)
	{
		switch ($name) {
			case 'production':
				// detects production mode by server IP address
				if (isset($_SERVER['SERVER_ADDR']) || isset($_SERVER['LOCAL_ADDR'])) {
					$addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
					if (substr($addr, -4) === '.loc') {
						return FALSE;
					}
				}
		}

		return parent::detect();
	}



	/**
	 * @param string $file
	 * @param bool $environment
	 * @param string|array $prefixPath
	 * @return ServiceContainerBuilder
	 */
	public function addConfigFile($file, $environments = TRUE, $prefixPath = NULL)
	{
		$file = realpath(Nette\Environment::expand($file));
		$this->configFiles[$file] = array($file, (bool)$environments, $prefixPath ? (array)$prefixPath : array());
		return $this;
	}



	/**
	 * @return Nette\Config\Config
	 */
	protected function loadConfigs()
	{
		$name = Environment::getName();
		$configs = array();

		// read and return according to actual environment name
		foreach ($this->configFiles as $file => $config) {
			$configs[$file] = Nette\Config\Config::fromFile(Nette\Environment::expand($config[0]), $config[1] ? $name : NULL);
		}

		$mergedConfig = array();
		foreach ($this->configFiles as $file => $config) {
			$appendConfig = array();

			$prefixed = &$appendConfig;
			foreach ($config[2] as $prefix) {
				$prefixed = &$prefixed[$prefix];
			}
			$prefixed = $configs[$file]->toArray();

			$mergedConfig = array_replace_recursive($mergedConfig, $appendConfig);
		}

		return new Nette\Config\Config($mergedConfig);
	}



	/**
	 * Loads global configuration from file and process it.
	 * @param  string|Nette\Config\Config  file name or Config object
	 * @return Nette\Config\Config
	 */
	public function loadConfig($file)
	{
		if ($file) {
			$this->addConfigFile($file);
		}

		return $this->serviceContainerBuilder->loadConfig($this->loadConfigs());
	}



	/**
	 * @return Kdyby\DependencyInjection\IServiceContainer
	 */
	public function createContext()
	{
		return $this->serviceContainerBuilder->createServiceContainer();
	}

}