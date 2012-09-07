<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Package\DoctrinePackage;

use Kdyby\Doctrine\Type;
use Nette;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;



/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Filip Procházka <filip@prochazka.su>
 */
class ConnectionFactory extends Nette\Object
{

	/**
	 * @var array
	 */
	private $typesConfig = array();

	/**
	 * @var boolean
	 */
	private $initialized = false;



	/**
	 * @param array $typesConfig
	 */
	public function __construct(array $typesConfig = NULL)
	{
		$this->typesConfig = (array)$typesConfig;
	}



	/**
	 * Create a connection by name.
	 *
	 * @param array $params
	 * @param \Doctrine\DBAL\Configuration $config
	 * @param \Doctrine\Common\EventManager  $eventManager
	 * @param array $mappingTypes
	 *
	 * @return \Doctrine\DBAL\Connection
	 */
	public function createConnection(array $params, Configuration $config = NULL, EventManager $eventManager = NULL, array $mappingTypes = array())
	{
		if (!$this->initialized) {
			$this->initializeTypes();
			$this->initialized = true;
		}

		/** @var \Doctrine\DBAL\Connection $connection */
		$connection = DriverManager::getConnection($params, $config, $eventManager);
		$platform = $connection->getDatabasePlatform();

		if (!empty($mappingTypes)) {
			foreach ($mappingTypes as $dbType => $doctrineType) {
				$platform->registerDoctrineTypeMapping($dbType, $doctrineType);
			}
		}

		if (!empty($this->typesConfig)) {
			foreach ($this->typesConfig as $type => $className) {
				$platform->markDoctrineTypeCommented(Type::getType($type));
			}
		}

		return $connection;
	}



	/**
	 * Registers Doctrine DBAL types
	 */
	private function initializeTypes()
	{
		foreach ($this->typesConfig as $type => $className) {
			if (Type::hasType($type)) {
				Type::overrideType($type, $className);

			} else {
				Type::addType($type, $className);
			}
		}
	}

}
