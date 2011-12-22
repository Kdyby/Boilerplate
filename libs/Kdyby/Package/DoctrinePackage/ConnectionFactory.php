<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kdyby\Package\DoctrinePackage;

use Kdyby\Doctrine\Type;
use Nette;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;



/**
 * Connection
 */
class ConnectionFactory extends Nette\Object
{
	/** @var array */
    private $typesConfig = array();

	/** @var boolean */
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

        $connection = DriverManager::getConnection($params, $config, $eventManager);

        if (!empty($mappingTypes)) {
            $platform = $connection->getDatabasePlatform();
            foreach ($mappingTypes as $dbType => $doctrineType) {
                $platform->registerDoctrineTypeMapping($dbType, $doctrineType);
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
