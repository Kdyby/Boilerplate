<?php

namespace Kdyby\Application;

use Nette;
use Nette\Application\InvalidPresenterException;
use Kdyby;



class PresenterFactoryChain extends Nette\Object implements Nette\Application\IPresenterFactory
{
	/** @var bool */
	public $caseSensitive = TRUE; // internationaly

	/** @var Kdyby\Injection\ServiceLoader */
	private $serviceLoader;

	/** @var array of Kdyby\Application\PresenterLoaders\IPresenterLoader */
	private $presenterLoaders = array();

	/** @var Nette\IContext */
	private $context;

	/** @var array */
	private $cache = array();



	/**
	 * @param string
	 */
	public function __construct(Nette\IContext $context)
	{
		$this->context = $context;
		$this->serviceLoader = new Kdyby\Injection\ServiceLoader($context);
	}



	/**
	 * @return Kdyby\Injection\ServiceLoader
	 */
	public function getServiceLoader()
	{
		return $this->serviceLoader;
	}



	/**
	 * @param Kdyby\Application\PresenterLoaders\IPresenterLoader $presenterLoader
	 */
	public function addPresenterLoader(PresenterLoaders\IPresenterLoader $presenterLoader)
	{
		$this->presenterLoaders[] = $presenterLoader;
	}



	/**
	 * @return array of Kdyby\Application\PresenterLoaders\IPresenterLoader
	 */
	public function getPresenterLoaders()
	{
		return $this->presenterLoaders;
	}



	/**
	 * Create new presenter instance.
	 * @param  string  presenter name
	 * @return Nette\Application\IPresenter
	 */
	public function createPresenter($name)
	{
		$class = $this->getPresenterClass($name);

		$presenter = $this->getServiceLoader()->createInstanceOfService($class);
		$presenter->setContext($this->context);

		return $presenter;
	}



	/**
	 * @param  string $name
	 * @return string
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (isset($this->cache[$name])) {
			list($class, $name) = $this->cache[$name];
			return $class;
		}

		if (!is_string($name) || !Nette\String::match($name, "#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#")) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		foreach ($this->presenterLoaders as $presenterLoader) {
			$exception = NULL;

			try {
				$class = $presenterLoader->formatPresenterClass($name);

				if (!class_exists($class)) {
					// internal autoloading
					$file = $presenterLoader->formatPresenterFile($name);
					if (is_file($file) && is_readable($file)) {
						Nette\Loaders\LimitedScope::load($file);
					}

					if (!class_exists($class)) {
						throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found in '$file'.");
					}
				}

				$reflection = new Nette\Reflection\ClassReflection($class);
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
					} else {
						$this->cache[$name] = array($class, $realName);
						$name = $realName;
					}
				} else {
					$this->cache[$name] = array($class, $realName);
				}

			} catch (InvalidPresenterException $exception) {
				$presenterLoader->addError($exception);
				continue;
			}
		}

		if ($exception) {
			throw $exception;
		}
	}

}