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

		// http
		$container->getDefinition('user')
			->setClass('Kdyby\Http\User');

		// application
		$container->addDefinition('application_storedRequestsManager')
			->setClass('Kdyby\Application\RequestManager', array('@application', '@session'));

		$container->getDefinition('presenterFactory')
			->setClass('Kdyby\Application\PresenterManager', array('@application_packageManager', '@container', '%appDir%'));

		$container->addDefinition('application_packageManager')
			->setClass('Kdyby\Packages\PackageManager');

		// console
		$container->addDefinition('console_helpers')
			->setClass('Symfony\Component\Console\Helper\HelperSet')
			->addSetup('set', array('@console_helper_serviceContainer', 'di'))
			->addSetup('set', array('@console_helper_ormEntityManager', 'em'))
			->addSetup('set', array('@console_helper_dbalConnection', 'db'));

		$container->addDefinition('console_helper_serviceContainer')
			->setClass('Kdyby\Console\ContainerHelper', array('@container'));

		$container->addDefinition('console_helper_ormEntityManager')
			->setClass('Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper', array('@doctrine_orm_entityManager'));

		$container->addDefinition('console_helper_dbalConnection')
			->setClass('Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper', array('@doctrine_dbal_connection'));

		// use tags Symfony\Component\Console\Helper\HelperInterface

		// cache
		$container->addDefinition('phpFileStorage')
			->setFactory('@templateCacheStorage');

		// security
		$container->addDefinition('authenticator')
			->setClass('Kdyby\Security\Authenticator', array('@security_identityDao'));

		$container->addDefinition('authorizator')
			->setClass('Nette\Security\IAuthorizator')
			->setFactory('@security_authorizatorFactory::create');

		$container->addDefinition('security_authorizatorFactory')
			->setClass('Kdyby\Security\AuthorizatorFactory', array('@user', '@session', '@doctrine'))
			->setShared(FALSE)
			->setInternal(TRUE);

		$container->addDefinition('security_identityDao')
			->setFactory('@doctrine::getDao', array('Kdyby\Security\Identity'))
			->setInternal(TRUE)
			->setShared(FALSE);

		// template
		$container->addDefinition('latte')
			->setClass('Nette\Latte\Engine');

		$container->addDefinition('templateFactory')
			->setClass('Kdyby\Templates\TemplateFactory', array('@latte', '@httpContext', '@user', '@templateCacheStorage', '@cacheStorage'));
	}

}
