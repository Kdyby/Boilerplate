<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Symfony\Component\Console;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property-read Kdyby\Doctrine\Workspace $workspace
 * @property-read Kdyby\Doctrine\Cache $doctrineCache
 * @property-read Kdyby\Doctrine\ORM\Container $sqldb
 * @property-read Kdyby\Doctrine\ORM\ContainerBuilder $sqldbContainerBuilder
 * @property-read Kdyby\Doctrine\ODM\Container $couchdb
 *
 * @property-read Console\Helper\HelperSet $consoleHelpers
 * @property-read Kdyby\Tools\FreezableArray $consoleCommands
 * @property-read Console\Application $console
 *
 * @property-read Nette\Application\Application $application
 * @property-read Nette\Application\IPresenterFactory $presenterFactory
 * @property-read Kdyby\Application\ModuleCascadeRegistry $moduleRegistry
 * @property-read Kdyby\Application\RequestManager $requestManager
 * @property-read Kdyby\Config\Settings $settings
 *
 * @property-read Nette\Application\IRouter $router
 * @property-read Nette\Http\Request $httpRequest
 * @property-read Nette\Http\Response $httpResponse
 * @property-read Nette\Http\Context $httpContext
 * @property-read Nette\Http\Session $session
 *
 * @property-read Nette\Http\User $user
 * @property-read Kdyby\Security\Users $users
 *
 * @property-read Kdyby\Templates\ITemplateFactory $templateFactory
 * @property-read Nette\Caching\Storages\PhpFileStorage $templateCacheStorage
 * @property-read Nette\Latte\Engine $latteEngine
 *
 * @property-read Nette\Loaders\RobotLoader $robotLoader
 *
 * @property-read Kdyby\Doctrine\Mapping\TypeMapper $doctrineTypeMapper
 * @property-read Kdyby\Doctrine\Mapping\EntityValuesMapper $doctrineEntityValuesMapper
 * @property-read Kdyby\Forms\Mapping\EntityFormMapperFactory $entityFormMapperFactory
 * @property-read Kdyby\Forms\EntityFormFactory $entityFormFactory
 *
 * @property-read Nette\Caching\IStorage $cacheStorage
 * @property-read Nette\Caching\Storages\IJournal $cacheJournal
 *
 * @property-read Nette\Mail\IMailer $mailer
 *
 * @property-read Kdyby\Modules\InstallWizard $installWizard
 */
class Container extends Nette\DI\Container
{

	/**
	 * @param string $key
	 * @param string|NULL $default
	 * @throws Nette\OutOfRangeException
	 * @return mixed
	 */
	public function getParam($key, $default = NULL)
	{
		if (isset($this->params[$key])) {
			return $this->params[$key];

		} elseif (func_num_args()>1) {
			return $default;
		}

		throw new Nette\OutOfRangeException("Missing key '$key' in " . get_class($this) . '->params');
	}



	/**
	 * @param string $name
	 * @param Nette\DI\IContainer $container
	 */
	public function lazyCopy($name, Nette\DI\IContainer $container)
	{
		$this->addService($name, function() use ($name, $container) {
			return $container->getService($name);
		});
	}



	/**
	 * Adds the specified service or service factory to the container.
	 * @param  string
	 * @param  mixed   object, class name or callback
	 * @param  mixed   array of tags or string typeHint
	 * @return Container|ServiceBuilder  provides a fluent interface
	 */
	public function addService($name, $service, $tags = NULL)
	{
		if (substr_count($name, '.') !== 0) {
			throw new Nette\InvalidArgumentException("Service name cannot contain dot.");
		}

		return parent::addService($name, $service, $tags);
	}



	/**
	 * Gets the service object by name.
	 * @param  string
	 * @return object
	 */
	public function getService($name)
	{
		if (substr_count($name, '.') === 0) {
			return parent::getService($name);
		}

		list($containerName, $serviceName) = explode('.', $name, 2);
		$container = parent::getService($containerName);
		if (!$container instanceof Nette\DI\IContainer) {
			throw new Nette\DI\MissingServiceException("Container '$containerName' not found.");
		}

		return $container->getService($serviceName);
	}



	/**
	 * Does the service exist?
	 * @param  string service name
	 * @return bool
	 */
	public function hasService($name)
	{
		if (substr_count($name, '.') === 0) {
			return parent::hasService($name);
		}

		list($containerName, $serviceName) = explode('.', $name, 2);
		$container = parent::getService($containerName);
		if (!$container instanceof Nette\DI\IContainer) {
			throw new Nette\DI\MissingServiceException("Container '$name' not found.");
		}

		return $container->hasService($serviceName);
	}

}