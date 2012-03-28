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
use Nette\DI\ServiceDefinition;
use Nette\Utils\Validators;
use Nette\Reflection\ClassType;
use Nette\Utils\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FrameworkExtension extends Kdyby\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		// watch for package files to change
		foreach ($container->parameters['kdyby']['packages'] as $packageClass) {
			$container->addDependency(ClassType::from($packageClass)->getFileName());
		}

		foreach ($this->compiler->getExtensions() as $extension) {
			$container->addDependency(ClassType::from($extension)->getFileName());
		}

		// application
		$container->getDefinition('nette.presenterFactory')
			->setClass('Kdyby\Application\PresenterManager', array('@kdyby.packageManager', '@container', '%appDir%'));

		$container->addDefinition($this->prefix('packageManager'))
			->setClass('Kdyby\Packages\PackageManager');

		// console
		$container->addDefinition($this->prefix('console.helpers'))
			->setClass('Symfony\Component\Console\Helper\HelperSet');

		$container->addDefinition($this->prefix('console.helper.serviceContainer'))
			->setClass('Kdyby\Console\ContainerHelper', array('@container'))
			->addTag('console.helper', array('alias' => 'di'));

		$container->addDefinition($this->prefix('console.helper.packageManager'))
			->setClass('Kdyby\Console\PackageManagerHelper', array($this->prefix('@packageManager')))
			->addTag('console.helper', array('alias' => 'pm'));

		$container->addDefinition($this->prefix('console.helper.ormEntityManager'))
			->setClass('Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper', array('@doctrine.orm.entityManager'))
			->addTag('console.helper', array('alias' => 'em'));

		$container->addDefinition($this->prefix('console.helper.dbalConnection'))
			->setClass('Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper', array('@doctrine.dbal.connection'))
			->addTag('console.helper', array('alias' => 'db'));

		$container->addDefinition($this->prefix('console.helper.cacheStorage'))
			->setClass('Kdyby\Console\StorageHelper', array($this->prefix('@cacheStorage')))
			->addTag('console.helper', array('alias' => 'cacheStorage'));

		$container->addDefinition($this->prefix('console.helper.phpFileStorage'))
			->setClass('Kdyby\Console\StorageHelper', array($this->prefix('@phpFileStorage')))
			->addTag('console.helper', array('alias' => 'phpFileStorage'));

		$container->addDefinition($this->prefix('console.helper.dialogHelper'))
			->setClass('Symfony\Component\Console\Helper\DialogHelper')
			->addTag('console.helper', array('alias' => 'dialog'));

		// cache
		$this->addAlias($this->prefix('phpFileStorage'), 'nette.templateCacheStorage');
		$this->addAlias($this->prefix('cacheStorage'), 'cacheStorage');

		// security
		$container->getDefinition('nette.userStorage')
			->setClass('Kdyby\Security\UserStorage', array('@session', $this->prefix('@security.identityDao')));

		$container->getDefinition('user')
			->setClass('Kdyby\Security\User', array('@nette.userStorage', '@container', $this->prefix('@security.identityDao')));

		$container->addDefinition('nette.authenticator')
			->setFactory('@user');

		$container->addDefinition($this->prefix('security.identityDao'))
			->setFactory('@doctrine.registry::getDao', array('Kdyby\Security\Identity'))
			->setInternal(TRUE);

		$container->addDefinition('nette.authorizator')
			->setClass('Nette\Security\IAuthorizator')
			->setFactory($this->prefix('@security.authorizatorFactory::create'));

		$container->addDefinition($this->prefix('security.authorizatorFactory'))
			->setClass('Kdyby\Security\AuthorizatorFactory', array('@user', '@session', '@doctrine.registry'))
			->setInternal(TRUE);

		// template
		$container->addDefinition($this->prefix('templateConfigurator'))
			->setClass('Kdyby\Templates\TemplateConfigurator');

		$container->addDefinition($this->prefix('editableTemplates'))
			->setClass('Kdyby\Templates\EditableTemplates', array(
				'@doctrine.registry', $this->prefix('@editableTemplates.storage')
			));

		// cache
		$container->addDefinition($this->prefix('editableTemplates.storage'))
			->setClass('Kdyby\Caching\LatteStorage', array('%tempDir%/cache', '@nette.cacheJournal'))
			->setAutowired(FALSE);

		// macros
		$this->addMacro('macros.core', 'Kdyby\Templates\CoreMacros::install');

		// curl
		$container->addDefinition($this->prefix('curl'))
			->setClass('Kdyby\Curl\CurlSender');

		$container->addDefinition($this->prefix('browser.panel'))
			->setFactory('Kdyby\Browser\Diagnostics\Panel::register')
			->addTag('run', TRUE);

		$container->addDefinition($this->prefix('curl.panel'))
			->setFactory('Kdyby\Curl\Diagnostics\Panel::register')
			->addTag('run', TRUE);
	}



	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();

		$this->registerConsoleHelpers($container);
		$this->registerMacroFactories($container);
		$this->unifyComponents($container);

		$routes = array();
		foreach ($container->findByTag('route') as $route => $meta) {
			$priority = isset($meta['priority']) ? $meta['priority'] : (int)$meta;
			$routes[$priority][] = $route;
		}

		krsort($routes);
		$router = $container->getDefinition('router');
		foreach (Kdyby\Tools\Arrays::flatMap($routes) as $route) {
			$router->addSetup('$service[] = $this->getService(?)', array($route));
		}
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	protected function registerConsoleHelpers(ContainerBuilder $container)
	{
		$helpers = $container->getDefinition($this->prefix('console.helpers'));

		foreach ($container->findByTag('console.helper') as $helper => $meta) {
			$alias = isset($meta['alias']) ? $meta['alias'] : NULL;
			$helpers->addSetup('set', array('@' . $helper, $alias));
		}
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	protected function registerMacroFactories(ContainerBuilder $container)
	{
		$config = $container->getDefinition($this->prefix('templateConfigurator'));

		foreach ($container->findByTag('latte.macro') as $factory => $meta) {
			$config->addSetup('addFactory', array($factory));
		}
	}



	/**
	 * Unifies component & presenter definitions using tags.
	 *
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	protected function unifyComponents(ContainerBuilder $container)
	{
		foreach ($container->findByTag('component') as $name => $meta) {
			$component = $container->getDefinition($name);

			if (!$component->parameters) {
				$component->setParameters(array());

			} else {
				$component->setAutowired(FALSE)->setShared(FALSE);
			}

			if ($this->componentHasTemplate($meta) && !$this->hasTemplateConfigurator($component)) {
				$component->addSetup('setTemplateConfigurator', array($this->prefix('@templateConfigurator')));
			}
		}
	}



	/**
	 * @param array $meta
	 * @return bool
	 */
	private function componentHasTemplate($meta)
	{
		return !isset($meta['template'])
			|| (isset($meta['template']) && $meta['template'] === TRUE);
	}



	/**
	 * @param \Nette\DI\ServiceDefinition $def
	 *
	 * @return bool
	 */
	private function hasTemplateConfigurator(ServiceDefinition $def)
	{
		foreach ($def->setup as $setup) {
			if ($setup->entity === 'setTemplateConfigurator') {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * @param \Nette\Utils\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Code\ClassType $class)
	{
		$this->compileConfigurator($class);
		$init = $class->methods['initialize'];

		$config = $this->getConfig();
		if (!empty($config['debugger']['browser'])) {
			$init->addBody('Kdyby\Diagnostics\ConsoleDebugger::enable(?);', array(
				$config['debugger']['browser']
			));
		}
	}



	/**
	 * @param \Nette\Utils\PhpGenerator\ClassType $class
	 */
	protected function compileConfigurator(Code\ClassType $class)
	{
		$container = $this->getContainerBuilder();
		/** @var \Nette\DI\ServiceDefinition $def */
		foreach ($container->getDefinitions() as $name => $def) {
			if ($def->class == 'Nette\DI\NestedAccessor' || $def->class === 'Nette\Callback' || $name === 'container' || !$def->shared) {
				continue;
			}

			$createBody = $class->methods[Nette\DI\Container::getMethodName($name)]->body;
			if ($lines = Nette\Utils\Strings::split($createBody, '~;[\n\r]*~mi')) {
				array_shift($lines); // naive: first line is creation

				$configure = $class->addMethod('configure' . ucfirst(strtr($name, '.', '_')));
				$configure->visibility = 'private';
				$configure->addParameter('service')->typeHint = $def->class;
				$configure->setBody(implode(";\n", $lines));
			}
		}

		$configure = $class->addMethod('configureService');
		$configure->addParameter('name');
		$configure->addParameter('service');
		$configure->setBody(
			'$this->{"configure" . ucfirst(strtr($name, ".", "_"))}($service);' . "\n" .
			'if ($this->hasService($name)) {' . "\n" .
			'	$this->removeService($name);' . "\n" .
			'}' . "\n" .
			'$this->addService($name, $service);'
		);
	}

}
