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
use Kdyby\Tools\FreezableArray;
use Nette;



/**
 * @author Filip Procházka
 */
class PresenterFactory extends Nette\Object implements Nette\Application\IPresenterFactory
{

	/** @var bool */
	public $caseSensitive = FALSE;

	/** @var array */
	private $cache = array();

	/** @var Nette\DI\IContainer */
	private $context;

	/** @var FreezableArray */
	private $moduleRegister;



	/**
	 * @param FreezableArray $moduleRegister
	 * @param Nette\DI\IContainer $context
	 */
	public function __construct(FreezableArray $moduleRegister, Nette\DI\IContainer $context)
	{
		$this->context = $context;
		$this->moduleRegister = $moduleRegister;
	}



	/**
	 * @param  string  presenter name
	 * @return string  class name
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (isset($this->cache[$name])) {
			list($class, $name) = $this->cache[$name];
			return $class;
		}

		if (!is_string($name) || !Nette\Utils\Strings::match($name, "#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#")) {
			throw InvalidPresenterException::invalidName($name);
		}

		$exception = NULL;
		foreach ($this->moduleRegister as $ns => $baseDir) {
			$class = (!is_numeric($ns) ? $ns . '\\' : NULL) . $this->formatPresenterClass($name);

			if (!class_exists($class))  { // internal autoloading
				$file = $this->formatPresenterFile($name, $baseDir);
				if (is_file($file) && is_readable($file)) {
					Nette\Utils\LimitedScope::load($file);
				}

				if (!class_exists($class)) {
					$exception = InvalidPresenterException::missing($name, $class, $file, $exception);
				}

			} else {
				break;
			}
		}

		if ($exception && !class_exists($class)) {
			throw $exception;
		}

		$reflection = new Nette\Reflection\ClassType($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw InvalidPresenterException::doesNotImplementInterface($name, $class);
		}

		if ($reflection->isAbstract()) {
			throw InvalidPresenterException::isAbstract($name, $class);
		}

		// canonicalize presenter name
		$realName = $this->unformatPresenterClass(substr($class, $ns ? strlen($ns)+1 : 0));
		if ($name !== $realName) {
			if ($this->caseSensitive) {
				throw InvalidPresenterException::caseSensitive($name, $realName);
			} else {
				$this->cache[$name] = array($class, $realName);
				$name = $realName;
			}

		} else {
			$this->cache[$name] = array($class, $realName);
		}

		return $class;
	}



	/**
	 * Create new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		$class = $this->getPresenterClass($name);

		$presenter = new $class;
		$presenter->setContext($this->context);

		return $presenter;
	}



	/**
	 * Formats presenter class name from its name.
	 * @param string $presenter
	 * @return string
	 */
	public function formatPresenterClass($presenter)
	{
		if (strpos($presenter, ':') === FALSE) {
			throw InvalidPresenterException::presenterNoModule($presenter);
		}

		return str_replace(':', '\\', $presenter) . 'Presenter';
	}



	/**
	 * Formats presenter name from class name.
	 * @param string $class
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		if (strpos($class, '\\') === FALSE) {
			throw InvalidPresenterException::classNoModule($class);
		}

		return str_replace('\\', ':', substr($class, 0, -9));
	}



	/**
	 * Formats presenter class file name.
	 * @param string $presenter
	 * @param string $baseDir
	 * @return string
	 */
	public function formatPresenterFile($presenter, $baseDir = APP_DIR)
	{
		if (strpos($presenter, ':') === FALSE) {
			throw InvalidPresenterException::presenterNoModule($presenter);
		}

		$path = '/' . str_replace(':', '/', $presenter);
		return $baseDir . substr_replace($path, '/presenters', strrpos($path, '/'), 0) . 'Presenter.php';
	}

}