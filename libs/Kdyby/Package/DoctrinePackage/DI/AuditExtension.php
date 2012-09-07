<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Package\DoctrinePackage\DI;

use Kdyby;
use Nette;
use Nette\DI\Statement;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * DbalExtension is an extension for the Doctrine DBAL library.
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
class AuditExtension extends Kdyby\Config\CompilerExtension
{

	/**
	 * @var array
	 */
	public $auditDefaults = array(
		'prefix' => '',
		'suffix' => '_audit',
		'tableName' => 'revisions',
	);

	/**
	 * @var array
	 */
	private $managers = array();



	/**
	 */
	public function loadConfiguration()
	{
		$builder = parent::loadConfiguration();
		$config = $this->getConfig($this->auditDefaults);

		$this->managers = array();
		foreach ($builder->parameters['doctrine']['entityManagers'] as $name => $id) {
			$this->loadAuditManager($name, $id, $config);
		}

		$builder->parameters['doctrine']['auditManagers'] = $this->managers;
	}



	/**
	 * @param string $name
	 * @param string $emId
	 * @param array $config
	 */
	private function loadAuditManager($name, $emId, array $config)
	{
		$builder = $this->getContainerBuilder();

		$configurator = $this->prefix($name . '.configuration');
		$builder->addDefinition($configurator)
			->setClass('Kdyby\Doctrine\Audit\AuditConfiguration')
			->addSetup('$prefix', array($config['prefix']))
			->addSetup('$suffix', array($config['suffix']))
			->addSetup('$tableName', array($config['tableName']));

		$this->managers[$name] = $manager = $this->prefix($name . '.manager');
		$builder->addDefinition($manager)
			->setClass('Kdyby\Doctrine\Audit\AuditManager', array('@' . $configurator, '@' . $emId));

		$builder->addDefinition($this->prefix($name . '.listener.createSchema'))
			->setClass('Kdyby\Doctrine\Audit\Listener\CreateSchemaListener', array('@' . $manager))
			->addTag('doctrine.eventSubscriber');

		$builder->addDefinition($this->prefix($name . '.listener.currentUser'))
			->setClass('Kdyby\Doctrine\Audit\Listener\CurrentUserListener', array('@' . $configurator))
			->addTag('doctrine.eventSubscriber');
	}

}
