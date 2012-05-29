<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Config;

use Kdyby;
use Nette;
use Nette\DI\Container;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method \Nette\DI\ContainerBuilder getContainerBuilder() getContainerBuilder()
 */
class CompilerExtension extends Nette\Config\CompilerExtension implements Kdyby\Packages\IPackageAware
{

	/**
	 * @var \Kdyby\Packages\Package
	 */
	private $package;



	/**
	 * @internal
	 * @param \Kdyby\Packages\Package $package
	 */
	public function setPackage(Kdyby\Packages\Package $package)
	{
		$this->package = $package;
	}



	/**
	 * Tries to load default '<compilerExtName>.neon' configuration file
	 */
	public function loadConfiguration()
	{
		if (!$this->package) {
			return;
		}

		$configDir = $this->package->getPath() . '/Resources/config';
		if (file_exists($configFile = $configDir . '/' . $this->name . '.neon')) {
			$this->compiler->parseServices(
				$this->getContainerBuilder(),
				$this->loadFromFile($configFile)
			);
		}
	}



	/**
	 * @param string $alias
	 * @param string $service
	 *
	 * @return \Nette\DI\ServiceDefinition
	 */
	public function addAlias($alias, $service)
	{
		$def = $this->getContainerBuilder()
			->addDefinition($alias);
		$def->setFactory('@' . $service);
		return $def;
	}



	/**
	 * Supply the name, and installer in format Class::install
	 * Installer method will receive Latter\Parser as first argument
	 *
	 * @param string $name
	 * @param string $installer
	 * @return \Nette\DI\ServiceDefinition
	 */
	public function addMacro($name, $installer)
	{
		$builder = $this->getContainerBuilder();

		$macro = $builder->addDefinition($name = $this->prefix($name))
			->setClass(substr($installer, 0, strpos($installer, '::')))
			->setFactory($installer, array('%compiler%'))
			->setParameters(array('compiler'))
			->addTag('latte.macro');

		$builder->getDefinition('nette.latte')
			->addSetup('$this->' . Container::getMethodName($name, FALSE) . '(?->compiler)', array('@self'));

		return $macro;
	}



	/**
	 * Intersects the keys of defaults and given options and returns only not NULL values.
	 *
	 * @param array $given	   Configurations options
	 * @param array $defaults  Defaults
	 * @param bool $keepNull
	 *
	 * @return array
	 */
	public static function getOptions(array $given, array $defaults, $keepNull = FALSE)
	{
		$options = array_intersect_key($given, $defaults) + $defaults;

		if ($keepNull === TRUE) {
			return $options;
		}

		return array_filter($options, function ($value) {
			return $value !== NULL;
		});
	}

}
