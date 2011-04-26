<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;
use Nette\Application\InvalidPresenterException;



/**
 * Kdyby presenter factory
 *
 * @author	Patrik Votoček
 * @author  Filip Procházka
 */
class PresenterFactory extends Nette\Object implements Nette\Application\IPresenterFactory, Kdyby\DependencyInjection\IContainerAware
{
	/** @var bool */
	public $caseSensitive = FALSE;

	/** @var Kdyby\Tools\FreezableArray */
	private $namespacePrefixes;

	/** @var Kdyby\DependencyInjection\IServiceContainer */
	private $serviceContainer;



	/**
	 * @param Kdyby\Tools\FreezableArray $context
	 */
	public function __construct(Kdyby\Tools\FreezableArray $namespacePrefixes)
	{
		$this->namespacePrefixes = $namespacePrefixes;
	}



	/**
	 * @param Kdyby\DependencyInjection\IServiceContainer $serviceContainer
	 */
	public function setServiceContainer(Kdyby\DependencyInjection\IServiceContainer $serviceContainer)
	{
		$this->serviceContainer = $serviceContainer;
	}



	/**
	 * @return Kdyby\DependencyInjection\IServiceContainer
	 */
	public function getServiceContainer()
	{
		return $this->serviceContainer;
	}



	/**
	 * Create new presenter instance.
	 * 
	 * @param  string  presenter name
	 * @return Nette\Application\IPresenter
	 */
	public function createPresenter($name)
	{
		$class = $this->getPresenterClass($name);
		$ref = Kdyby\Reflection\ServiceReflection::from($class);
		$params = $ref->getConstructorParamClasses();

		$serviceContainer = $this->getServiceContainer();
		$presenter = $params ? $ref->newInstanceArgs($serviceContainer->expandParameters($params)) : new $class;
		if ($presenter instanceof Kdyby\DependencyInjection\IContainerAware) {
			$presenter->setServiceContainer($serviceContainer);
		}

	    return $presenter;
	}


	
	/**
	 * Format presenter class with prefixes
	 *
	 * @param string
	 * @return string
	 * @throws \InvalidPresenterException
	 */
	private function formatPresenterClasses($name)
	{
		$class = NULL;
		foreach ($this->namespacePrefixes as $key => $namespace) {
			$class = $this->formatPresenterClass($name, $key);
			if (class_exists($class)) {
				break;
			}
		}

		if (!class_exists($class)) {
			$class = $this->formatPresenterClass($name);
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found.");
		}

		return $class;
	}

	/**
	 * Get presenter class name
	 *
	 * @param string
	 * @return string
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (!is_string($name) || !preg_match("#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#", $name)) {
			throw new InvalidPresenterException("Presenter name must be an alphanumeric string, '$name' is invalid.");
		}

		$class = $this->formatPresenterClasses($name);
		$reflection = new Nette\Reflection\ClassType($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		}

		if ($reflection->isAbstract()) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		// canonicalize presenter name
		$realName = $this->unformatPresenterClass($class);
		if ($name !== $realName) {
			if ($this->caseSensitive) {
				throw new InvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
			}
		}

		return $class;
	}



	/**
	 * Formats presenter class name from its name.
	 *
	 * @param string presenter name
	 * @param string
	 * @return string
	 */
	public function formatPresenterClass($presenter, $type = 'app')
	{
		$prefix = isset($this->namespacePrefixes[$type]) ? $this->namespacePrefixes[$type] : NULL;
		return $prefix . str_replace(':', 'Module\\', $presenter) . 'Presenter';
	}



	/**
	 * Formats presenter name from class name.
	 *
	 * @param string presenter class
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		$suitable = $this->namespacePrefixes->filter(function ($prefix) use ($class) {
			return Nette\Utils\Strings::startsWith($class, $prefix);
		});

		if (!$suitable) {
			throw new \InvalidArgumentException("Presenter prefix not found.");
		}

		return str_replace("Module\\", ':', substr($class, strlen(current($suitable)), -9));
	}

}