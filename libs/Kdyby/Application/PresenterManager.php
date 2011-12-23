<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Kdyby;
use Kdyby\Packages\PackageManager;
use Nette;
use Nette\Reflection\ClassType;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PresenterManager extends Nette\Application\PresenterFactory implements Nette\Application\IPresenterFactory
{

	/** @var \Nette\DI\Container */
	private $container;

	/** @var \Kdyby\Packages\PackageManager */
	private $packageManager;



	/**
	 * @param \Kdyby\Packages\PackageManager $packageManager
	 * @param \Nette\DI\Container $container
	 * @param string $appDir
	 */
	public function __construct(PackageManager $packageManager, Nette\DI\Container $container, $appDir)
	{
		parent::__construct($appDir, $container);

		$this->container = $container;
		$this->packageManager = $packageManager;
	}



	/**
	 * @param  string  presenter name
	 * @return string  class name
	 * @throws \Kdyby\Application\InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (!is_string($name) || !Strings::match($name, "#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#")) {
			throw InvalidPresenterException::invalidName($name);
		}

		if (!Strings::match($name, '~^[^:]+Package:[^:]+~i')) {
			return parent::getPresenterClass($name);
		}

		$serviceName = $this->formatServiceNameFromPresenter($name);
		if ($this->container->hasService($serviceName)) {
			$reflection = new ClassType($this->container->getService($serviceName));
			return $reflection->getName();
		}

		list($package, $shortName) = explode(':', $name, 2);
		$package = $this->packageManager->getPackage($package);

		$class = $this->formatClassFromPresenter($shortName, $package);

		if (!class_exists($class)) {
			throw InvalidPresenterException::missing($shortName, $class);
		}


		$reflection = new ClassType($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw InvalidPresenterException::doesNotImplementInterface($name, $class);
		}

		if ($reflection->isAbstract()) {
			throw InvalidPresenterException::isAbstract($name, $class);
		}

		// canonicalize presenter name
		if ($name !== $realName = $this->formatPresenterFromClass($class)) {
			if ($this->caseSensitive) {
				throw InvalidPresenterException::caseSensitive($name, $realName);

			} else {
				$name = $realName;
			}
		}

		return $class;
	}



	/**
	 * Finds presenter service in DI Container, or creates new object
	 * @param string $name
	 * @return \Nette\Application\IPresenter
	 */
	public function createPresenter($name)
	{
		$serviceName = $this->formatServiceNameFromPresenter($name);
		if ($this->container->hasService($serviceName)) {
			$presenter = $this->container->getService($serviceName);

		} else {
			$class = $this->getPresenterClass($name);
			$presenter = new $class($this->container);
		}

		if (method_exists($presenter, 'setContext')) {
			$this->container->callMethod(array($presenter, 'setContext'));
		}

		return $presenter;
	}



	/**
	 * @param string $presenterClass
	 * @return \Kdyby\Package\Package
	 */
	public function getPresenterPackage($presenterClass)
	{
		foreach ($this->packages as $package) {
			if (Strings::startsWith($presenterClass, $package->getNamespace())) {
				return $package;
			}
		}

		throw new Kdyby\InvalidArgumentException("Presenter $presenterClass does not belong to any active package.");
	}



	/**
	 * Formats service name from it's presenter name
	 *
	 * 'Bar:Foo:FooBar' => 'bar.foo.foo_bar_presenter'
	 *
	 * @param string $presenter
	 * @return string
	 */
	public function formatServiceNameFromPresenter($presenter)
	{
		return Strings::replace($presenter, '/(^|:)+(.)/', function ($match) {
			return (':' === $match[1] ? '_' : '') . strtolower($match[2]);
		}) . 'Presenter';
	}



	/**
	 * Formats presenter name from it's service name
	 *
	 * 'bar.foo.foo_bar_presenter' => 'Bar:Foo:FooBar'
	 *
	 * @param string $name
	 * @return string
	 */
	public function formatPresenterFromServiceName($name)
	{
		return Strings::replace(substr($name, 0, -9), '/(^|_)+(.)/', function ($match) {
			return ('_' === $match[1] ? ':' : '') . strtoupper($match[2]);
		});
	}



	/**
	 * Formats presenter class to it's name
	 *
	 * 'Kdyby\BarPackage\Presenter\FooFooPresenter' => 'Bar:FooFoo'
	 * 'Kdyby\BarPackage\Presenter\FooModule\FooBarPresenter' => 'Bar:Foo:FooBar'
	 *
	 * @param string $class
	 * @return string
	 */
	public function formatPresenterFromClass($class)
	{
		$m = Strings::match($class, '~^(?P<ns>.+\\\\)(?P<package>[^\\\\]+Package)\\\\Presenter\\\\(?P<presenter>.+Presenter)$~i');

		if ($m) {
			return $m['package'] . ':' . $this->unformatPresenterClass($m['presenter']);
		}
	}



	/**
	 * @param string $presenter
	 * @param \Kdyby\Packages\Package $package
	 */
	public function formatClassFromPresenter($presenter, Kdyby\Packages\Package $package)
	{
		return $package->getNamespace() . '\\Presenter\\' . $this->formatPresenterClass($presenter);
	}

}
