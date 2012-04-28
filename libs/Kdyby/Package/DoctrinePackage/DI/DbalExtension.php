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
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * DbalExtension is an extension for the Doctrine DBAL library.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DbalExtension extends Kdyby\Config\CompilerExtension
{

	/**
	 * @var array
	 */
	public $connectionDefaults = array(
		'dbname' => NULL,
		'host' => NULL,
		'port' => NULL,
		'user' => NULL,
		'password' => NULL,
		'charset' => NULL,
		'driver' => 'pdo_mysql',
		'driverClass' => NULL,
		'options' => NULL,
		'path' => NULL,
		'memory' => NULL,
		'unix_socket' => NULL,
		'wrapperClass' => 'Kdyby\Doctrine\Connection',
		'logging' => TRUE,
		'platformService' => NULL,
	);

	/**
	 * @var array
	 */
	public $driverDefaults = array(
		'pdo_mysql' => array(
			'host' => 'localhost',
			'port' => 3306,
			'charset' => 'UTF8',
		)
	);

	/**
	 * @var array
	 */
	protected $defaultTypes = array(
		Kdyby\Doctrine\Type::CALLBACK => 'Kdyby\Doctrine\Types\Callback',
		Kdyby\Doctrine\Type::PASSWORD => 'Kdyby\Doctrine\Types\Password'
	);



	/**
	 * dbal:
	 * 	dbname: database
	 * 	user: root
	 * 	password: 123
	 */
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig();

		$connections = isset($config['connections']) ? $config['connections'] : array('default' => $config);

		// default connection
		if (empty($config['defaultConnection'])) {
			$keys = array_keys($connections);
			$config['defaultConnection'] = reset($keys);
		}
		$container->parameters['doctrine']['defaultConnection'] = $config['defaultConnection'];

		// Validators::assertFields($config['types'], 'class')
		$types = $this->defaultTypes;
		if (isset($config['types'])) {
			Validators::assertField($config, 'types', 'array');
			$types = $config['types'] + $types;
		}
		$container->parameters['doctrine']['dbal']['connectionFactory']['types'] = $types;

		// connections list
		foreach (array_keys($connections) as $name) {
			$container->parameters['doctrine']['connections'][$name] = 'doctrine.dbal.' . $name . 'Connection';
		}

		// load connections
		foreach ($connections as $name => $connection) {
			$connection['name'] = $name;
			$this->loadConnection($container, $connection);
		}

		$this->addAlias('doctrine.dbal.connection', 'doctrine.dbal.' . $config['defaultConnection'] . 'Connection');
		$this->addAlias('doctrine.dbal.eventManager', 'doctrine.dbal.' . $config['defaultConnection'] . 'Connection.eventManager');
	}



	/**
	 * Loads a configured DBAL connection.
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	protected function loadConnection(ContainerBuilder $container, array $config)
	{
		$connectionName = 'doctrine.dbal.' . $config['name'] . 'Connection';

		// options
		$options = self::getOptions($config, $this->connectionDefaults);
		if (isset($this->driverDefaults[$options['driver']])) {
			$options += $this->driverDefaults[$options['driver']];
		}

		// configuration
		$configuration = $container->addDefinition($connectionName . '.configuration')
			->setClass('Doctrine\DBAL\Configuration');

		// logging
		$container->addDefinition($connectionName . '.logger')
			->setClass('Kdyby\Doctrine\Diagnostics\Panel')
			->setFactory('Kdyby\Doctrine\Diagnostics\Panel::register')
			->setAutowired(FALSE);

		if ($options['logging']) {
			$configuration->addSetup('setSQLLogger', array('@' . $connectionName . '.logger'));
		}

		// event manager
		$container->addDefinition($connectionName . '.eventManager')
			->setClass('Doctrine\Common\EventManager')
			->setAutowired(FALSE);

		// charset
		$this->loadConnectionCharset($container, $options, $connectionName);

		// connection factory
		$container->addDefinition($connectionName . '.factory')
			->setClass('Kdyby\Package\DoctrinePackage\ConnectionFactory', array('%doctrine.dbal.connectionFactory.types%'))
			->setInternal(TRUE)
			->setShared(FALSE);

		$mappingTypes = array();
		if (isset($config['mappingTypes'])) {
			Validators::assertField($config, 'mappingTypes', 'array');
			$mappingTypes = $config['mappingTypes'];
		}

		// connection
		$connection = $container->addDefinition($connectionName)
			->setClass('Doctrine\DBAL\Connection')
			->setFactory('@' . $connectionName . '.factory::createConnection', array(
				$options,
				'@' . $connectionName . '.configuration',
				'@' . $connectionName . '.eventManager',
				$mappingTypes
			));

		if ($options['logging']) {
			$connection->addSetup('$service->getConfiguration()->getSQLLogger()->setConnection(?)', array('@self'));
		}
	}



	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 * @param string $connectionName
	 */
	protected function loadConnectionCharset(ContainerBuilder $container, array $config, $connectionName)
	{
		if ($this->connectionUsesMysqlDriver($config)) {
			$container->addDefinition($connectionName . '.events.mysqlSessionInit')
				->setClass('Doctrine\DBAL\Event\Listeners\MysqlSessionInit', array($config['charset']));
		}
	}



	/**
	 * @param array $connection
	 *
	 * @return boolean
	 */
	protected function connectionUsesMysqlDriver(array $connection)
	{
		return (isset($connection['driver']) && stripos($connection['driver'], 'mysql') !== FALSE)
			|| (isset($connection['driverClass']) && stripos($connection['driverClass'], 'mysql') !== FALSE);
	}

}
