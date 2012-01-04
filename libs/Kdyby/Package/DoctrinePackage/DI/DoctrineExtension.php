<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\DoctrinePackage\DI;

use Kdyby;
use Nette;
use Nette\DI\ServiceDefinition;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * OrmExtension is an extension for the Doctrine ORM library.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DoctrineExtension extends Kdyby\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainer();
		
		$container->addDefinition('doctrine')
			->setClass('Kdyby\Package\DoctrinePackage\Registry', array(
				'@container',
				'%doctrine_connections%',
				'%doctrine_entityManagers%',
				'%doctrine_defaultConnection%',
				'%doctrine_defaultEntityManager%'
			));

		$container->addDefinition('doctrine_orm_events_discriminatorMapDiscovery')
			->setClass('Kdyby\Doctrine\Mapping\DiscriminatorMapDiscoveryListener', array('@doctrine_orm_metadata_annotationReader'))
			->addTag('doctrine_eventSubscriber');

		$container->addDefinition('doctrine_orm_events_entityDefaults')
			->setClass('Kdyby\Doctrine\Mapping\EntityDefaultsListener')
			->addTag('doctrine_eventSubscriber');
	}



	public function beforeCompile()
	{
		$this->registerEventSubscribers($this->getContainer());
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	protected function registerEventSubscribers(ContainerBuilder $container)
	{
		foreach ($container->findByTag('doctrine_eventSubscriber') as $listener => $meta) {
			if (isset($meta['connection'])) {
				$this->registerEventSubscriber($meta['connection'], $listener);

			} elseif (isset($meta['connections'])) {
				foreach ($meta['connections'] as $connection) {
					$this->registerEventSubscriber($connection, $listener);
				}

			} else {
				foreach (array_keys($container->parameters['doctrine_connections']) as $connection) {
					$this->registerEventSubscriber($connection, $listener);
				}
			}
		}
	}



	/**
	 * @param string $connectionName
	 * @param string $listener
	 */
	protected function registerEventSubscriber($connectionName, $listener)
	{
		$this->getConnectionEventManager($connectionName)
			->addSetup('addEventSubscriber', array('@' . $listener));
	}



	/**
	 * @param string $connectionName
	 * @return \Nette\DI\ServiceDefinition
	 */
	protected function getConnectionEventManager($connectionName)
	{
		$connections = $this->getContainer()->parameters['doctrine_connections'];
		Validators::assertField($connections, $connectionName);

		$connection = $this->getContainer()->parameters['doctrine_connections'][$connectionName];
		return $this->getContainer()->getDefinition($connection . '_eventManager');
	}

}
