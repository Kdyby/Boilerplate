<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing;

use Doctrine;
use DoctrineExtensions;
use Kdyby;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka
 */
abstract class OrmTestCase extends DoctrineExtensions\PHPUnit\OrmTestCase
{

	/** @var Kdyby\Doctrine\ORM\Container */
	private static $doctrineContainer;

	/** @var Kdyby\Application\Container */
	private $context;

	/** @var Kdyby\DI\Configurator */
	private $configurator;



	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		$this->configurator = Nette\Environment::getConfigurator();
		$this->context = $this->configurator->getContainer();
		parent::__construct($name, $data, $dataName);
	}



	/**
	 * @return Kdyby\DI\Configurator
	 */
	public function getConfigurator()
	{
		return $this->configurator;
	}



	/**
	 * @return Kdyby\Application\Container
	 */
	public function getContext()
	{
		return $this->context;
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	protected function createEntityManager()
	{
		if (self::$doctrineContainer === NULL) {
			self::$doctrineContainer = $container = new Kdyby\Doctrine\ORM\Container($this->getContext(), array(
				'driver' => 'pdo_sqlite',
				'dsn' => 'sqlite::memory:',
				'memory' => TRUE
			));

			$evm = $container->getEntityManager()->getEventManager();
			$evm->addEventSubscriber(new SchemaSetupListener());
		}

		return self::$doctrineContainer->getEntityManager();
	}



	/**
	 * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet()
	{
		return new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
	}



	/**
	 * @param string $entityName
	 * @return Doctrine\ORM\EntityRepository
	 */
	protected function getRepository($entityName)
	{
		return $this->getEntityManager()->getRepository($entityName);
	}



	/**
	 * @param string $className
	 * @return Doctrine\ORM\Mapping\ClassMetadata
	 */
	protected function getMetadata($className)
	{
		return $this->getEntityManager()->getClassMetadata($className);
	}



	/**
	 * @return string
	 */
	protected function getTableName($entityName)
	{
		return $this->getMetadata($entityName)->table['name'];
	}



	/**
	 * @param string $yamlFile
	 * @return \PHPUnit_Extensions_Database_DataSet_YamlDataSet
	 */
	protected function createYamlDataSet($yamlFile)
	{
		return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet($yamlFile);
	}



	/**
	 * @param string $neonFile
	 * @return Database\NeonDataSet
	 */
	protected function createNeonDataSet($neonFile)
	{
		return new Database\NeonDataSet($this->getConnection()->getMetaData(), $neonFile);
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}