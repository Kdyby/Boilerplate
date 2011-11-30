<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package;

use Kdyby;
use Nette;
use Nette\Application as App;
use Nette\Application\Application;
use Nette\Reflection\ClassType;
use Nette\Utils\Finder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Console\Application as ConsoleApp;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class Package extends Nette\Object implements IPackage
{

	/** @var string */
	protected $name;

	/** @var string */
	protected $version = '1';

	/** @var string */
	protected $extension;

	/** @var Kdyby\DI\IContainer */
	protected $container;



	/**
	 * @param Kdyby\DI\IContainer $container
	 */
	public function setContainer(Kdyby\DI\IContainer $container = NULL)
	{
		$this->container = $container;
	}



	/**
	 * Returns the Package name (the class short name)
	 *
	 * @return string
	 */
	final public function getName()
	{
		if ($this->name !== NULL) {
			return $this->name;
		}

		$name = get_class($this);
		$pos = strrpos($name, '\\');
		$shortName = FALSE === $pos ? $name :  substr($name, $pos + 1);
		return $this->name = $shortName;
	}



	/**
	 * Returns the Package namespace
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->reflection->getNamespaceName();
	}



	/**
	 * Returns the Package version
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}



	/**
	 * Returns the Package absolute directory path
	 *
	 * @return string
	 */
	public function getPath()
	{
		return dirname($this->reflection->getFileName());
	}



	/**
	 * Occurs before the application loads presenter
	 */
	public function onStartup()
	{
	}



	/**
	 * Occurs when a new request is ready for dispatch
	 *
	 * @param App\Request $request
	 */
	public function onRequest(App\Request $request)
	{
	}



	/**
	 * Occurs when a new response is received
	 *
	 * @param App\IResponse $response
	 */
	public function onResponse(App\IResponse $response)
	{
	}



	/**
	 * Occurs when an unhandled exception occurs in the application
	 *
	 * @param \Exception $e
	 */
	public function onError(\Exception $e)
	{
	}



	/**
	 * Occurs before the application shuts down
	 *
	 * @param \Exception|NULL $e
	 */
	public function onShutdown(\Exception $e = NULL)
	{
	}



	/**
	 * Returns the container extension that should be implicitly loaded
	 *
	 * @return ExtensionInterface|NULL
	 */
	public function getContainerExtension()
	{
		if ($this->extension === NULL) {
			$basename = preg_replace('/Package$/', '', $this->getName());

			$class = $this->getNamespace() . '\\DI\\' . $basename . 'Extension';
			if (!class_exists($class)) {
				$this->extension = FALSE;

			} else {
				$extension = new $class();

				// check naming convention
				$expectedAlias = Container::underscore($basename);
				if ($expectedAlias != $extension->getAlias()) {
					throw new \LogicException(
						'The extension alias for the default extension of a '.
						'package must be the underscored version of the '.
						'package name ("' . $expectedAlias . '" instead of "' . $extension->getAlias() . '")'
					);
				}

				$this->extension = $extension;
			}
		}

		if ($this->extension) {
			return $this->extension;
		}
	}


	/**
	 * Builds the Package. It is only ever called once when the cache is empty
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 *
	 */
	public function build(ContainerBuilder $container)
	{
	}



	/**
	 * Returns list of available migrations
	 *
	 * @return array
	 */
	public function getMigrations()
	{
		if (!$dir = realpath($this->getPath() . '/Migration')) {
			return;
		}

		$migrations = array();
		$ns = $this->getNamespace() . '\\Migration';
		foreach (Finder::findFiles('Version*.php')->in($dir) as $file) {
			$class = $ns . '\\' . $file->getBasename('.php');

			$refl = ClassType::from($class);
			if ($refl->isSubclassOf('Doctrine\\DBAL\\Migrations\\AbstractMigration') && !$refl->isAbstract()) {
				continue;
			}

			$migrations[] = $class;
		}

		return $migrations;
	}



	/**
	 * Installs the package
	 */
	public function install()
	{
	}



	/**
	 * Uninstalls the package
	 */
	public function uninstall()
	{
	}



	/**
	 * Finds and registers Commands.
	 *
	 * Override this method if your bundle commands do not follow the conventions:
	 *
	 * * Commands are in the 'Command' sub-directory
	 * * Commands extend Symfony\Component\Console\Command\Command
	 *
	 * @param ConsoleApp $app
	 */
	public function registerCommands(ConsoleApp $app)
	{
		if (!$dir = realpath($this->getPath() . '/Command')) {
			return;
		}

		$ns = $this->getNamespace() . '\\Command';
		foreach (Finder::findFiles('*Command.php')->from($dir) as $file) {
			$relative = strtr($file->getRealpath(), array($dir => '', '/' => '\\'));
			$class = $ns . '\\' . ltrim(substr($relative, 0, -4), '\\');
			$refl = ClassType::from($class);
			if ($refl->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$refl->isAbstract()) {
				$app->add($refl->newInstance());
			}
		}
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function registerPresenters(ContainerBuilder $container)
	{
		if (!$dir = realpath($this->getPath() . '/Presenter')) {
			return;
		}

		$ns = $this->getNamespace() . '\\Presenter';
		foreach (Finder::findFiles('*Presenter.php')->from($dir) as $file) {
			$relative = strtr($file->getRealpath(), array($dir => '', '/' => '\\'));
			$class = $ns . '\\' . ltrim(substr($relative, 0, -4), '\\');
			$refl = ClassType::from($class);
			if (!$refl->implementsInterface('Nette\Application\IPresenter') || $refl->isAbstract()) {
				continue;
			}

			$id = Kdyby\Application\PresenterManager::formatPresenterFromClass($refl->getName(), $this);

			// class name
			$container->setParameter($id . '.class', $refl->getName());

			// service definition
			$definition = new DefinitionDecorator('presenter_abstract');
			$definition->setClass("%$id.class%");
			$container->setDefinition($id, $definition);
		}
	}

}
