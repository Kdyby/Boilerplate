<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\FrameworkPackage\DI;

use Kdyby;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;
use Nette\Reflection\ClassType;
use Nette\Utils\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FrameworkExtension extends Kdyby\Config\CompilerExtension
{

	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		// watch for package files to change
		Validators::assertField($container->parameters, 'kdyby_packages', 'array');
		foreach ($container->parameters['kdyby_packages'] as $packageClass) {
			$container->addDependency(ClassType::from($packageClass)->getFileName());
		}

		foreach ($this->compiler->getExtensions() as $extension) {
			$container->addDependency(ClassType::from($extension)->getFileName());
		}

		// http
		$container->getDefinition('user')
			->setClass('Kdyby\Http\User', array('@session', '@container', '@security_identityDao'));

		// application
		$container->addDefinition('application_storedRequestsManager')
			->setClass('Kdyby\Application\RequestManager', array('@application', '@session'));

		$container->getDefinition('presenterFactory')
			->setClass('Kdyby\Application\PresenterManager', array('@application_packageManager', '@container', '%appDir%'));

		$container->addDefinition('application_packageManager')
			->setClass('Kdyby\Packages\PackageManager');

		// console
		$container->addDefinition('console_helpers')
			->setClass('Symfony\Component\Console\Helper\HelperSet');

		$container->addDefinition('console_helper_serviceContainer')
			->setClass('Kdyby\Console\ContainerHelper', array('@container'))
			->addTag('console_helper', array('alias' => 'di'));

		$container->addDefinition('console_helper_ormEntityManager')
			->setClass('Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper', array('@doctrine_orm_entityManager'))
			->addTag('console_helper', array('alias' => 'em'));

		$container->addDefinition('console_helper_dbalConnection')
			->setClass('Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper', array('@doctrine_dbal_connection'))
			->addTag('console_helper', array('alias' => 'db'));

		$container->addDefinition('console_helper_cacheStorage')
			->setClass('Kdyby\Console\StorageHelper', array('@cacheStorage'))
			->addTag('console_helper', array('alias' => 'cacheStorage'));

		$container->addDefinition('console_helper_phpFileStorage')
			->setClass('Kdyby\Console\StorageHelper', array('@phpFileStorage'))
			->addTag('console_helper', array('alias' => 'phpFileStorage'));

		// cache
		$container->addDefinition('phpFileStorage')
			->setFactory('@templateCacheStorage');

		// security
		$container->addDefinition('authenticator')
			->setFactory('@user');

		$container->addDefinition('security_identityDao')
			->setFactory('@doctrine::getDao', array('Kdyby\Security\Identity'))
			->setInternal(TRUE)
			->setShared(FALSE);

		$container->addDefinition('authorizator')
			->setClass('Nette\Security\IAuthorizator')
			->setFactory('@security_authorizatorFactory::create');

		$container->addDefinition('security_authorizatorFactory')
			->setClass('Kdyby\Security\AuthorizatorFactory', array('@user', '@session', '@doctrine'))
			->setShared(FALSE)
			->setInternal(TRUE);

		// template
		$container->addDefinition('templateConfigurator')
			->setClass('Kdyby\Templates\TemplateConfigurator');
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	public function beforeCompile(ContainerBuilder $container)
	{
		$this->registerConsoleHelpers($container);
		$this->registerMacroFactories($container);
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	protected function registerConsoleHelpers(ContainerBuilder $container)
	{
		$helpers = $container->getDefinition('console_helpers');

		foreach ($container->findByTag('console_helper') as $helper => $meta) {
			$alias = isset($meta['alias']) ? $meta['alias'] : NULL;
			$helpers->addSetup('set', array('@' . $helper, $alias));
		}
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	protected function registerMacroFactories(ContainerBuilder $container)
	{
		$config = $container->getDefinition('templateConfigurator');

		foreach ($container->findByTag('latte_macro') as $factory => $meta) {
			$config->addSetup('addFactory', array($factory));
		}
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param \Nette\Utils\PhpGenerator\ClassType $class
	 */
	public function afterCompile(ContainerBuilder $container, Code\ClassType $class)
	{
		$this->compileRouter($container, $class->methods['initialize']);
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param \Nette\Utils\PhpGenerator\Method $initialize
	 */
	protected function compileRouter(ContainerBuilder $container, Code\Method $initialize)
	{
		$routes = array();

		foreach ($container->findByTag('route') as $route => $meta) {
			$priority = isset($meta['priority']) ? $meta['priority'] : (int)$meta;
			$routes[$priority][] = $route;
		}

		krsort($routes);
		foreach (Kdyby\Tools\Arrays::flatMap($routes) as $route) {
			$initialize->addBody('$this->router[] = $this->?;', array($route));
		}
	}

}
