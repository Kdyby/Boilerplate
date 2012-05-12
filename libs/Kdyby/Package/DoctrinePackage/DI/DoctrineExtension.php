<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
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
		$container = $this->getContainerBuilder();

		$container->addDefinition($this->prefix('registry'))
			->setClass('Kdyby\Doctrine\Registry', array(
				'@container',
				'%doctrine.connections%',
				'%doctrine.entityManagers%',
				'%doctrine.defaultConnection%',
				'%doctrine.defaultEntityManager%',
				'%doctrine.auditManagers%',
			));

		$container->addDefinition($this->prefix('orm.events.discriminatorMapDiscovery'))
			->setClass('Kdyby\Doctrine\Mapping\DiscriminatorMapDiscoveryListener', array('@doctrine.orm.metadata.annotationReader'))
			->addTag('doctrine.eventSubscriber');

		$container->addDefinition($this->prefix('orm.events.entityDefaults'))
			->setClass('Kdyby\Doctrine\Mapping\EntityDefaultsListener')
			->addTag('doctrine.eventSubscriber');
	}



	/**
	 */
	public function beforeCompile()
	{
		$this->registerEventSubscribers($this->getContainerBuilder());
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $builder
	 */
	protected function registerEventSubscribers(ContainerBuilder $builder)
	{
		$connectionIds = array_keys($builder->parameters['doctrine']['connections']);

		foreach ($builder->findByTag('doctrine.eventSubscriber') as $listener => $meta) {
			if (isset($meta['connection'])) {
				$this->registerEventSubscriber($meta['connection'], $listener);

			} elseif (isset($meta['connections'])) {
				foreach ($meta['connections'] as $id) {
					$this->registerEventSubscriber($id, $listener);
				}

			} else {
				foreach ($connectionIds as $id) {
					$this->registerEventSubscriber($id, $listener);
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
		$this->getContainerBuilder()->getDefinition($listener)
			->addTag('doctrine.eventSubscriber.' . $connectionName);
	}

}
