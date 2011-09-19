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
use DoctrineExtensions\PHPUnit\TestConnection;
use DoctrineExtensions\PHPUnit\DatabaseTester;
use Kdyby;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka
 */
abstract class OrmTestCase extends \PHPUnit_Extensions_Database_TestCase
{

	/** @var Kdyby\Application\Container */
	private $context;

	/** @var Kdyby\DI\Configurator */
	private $configurator;

	/** @var Kdyby\Doctrine\ORM\Container */
	private static $doctrineContainer;

	/** @var DoctrineExtensions\PHPUnit\TestConnection */
	private static $connection;

	/** @var Doctrine\ORM\EntityManager */
	private static $em;



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
	protected function getConfigurator()
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
	 * Performs operation returned by getSetUpOperation().
	 */
	protected function setUp()
	{
		$this->databaseTester = NULL;

		$em = $this->getEntityManager();
		$eventManager = $em->getEventManager();
		if ($eventManager->hasListeners('preTestSetUp')) {
			$eventManager->dispatchEvent('preTestSetUp', new OrmTestCaseEventArgs($em, $this));
		}

		$tester = $this->getDatabaseTester();

		$tester->setSetUpOperation($this->getSetUpOperation());
		$tester->setDataSet($this->getDataSet());
		$tester->onSetUp();

		if ($eventManager->hasListeners('postTestSetUp')) {
			$eventManager->dispatchEvent('postTestSetUp', new OrmTestCaseEventArgs($em, $this));
		}
	}



	/**
	 * @return TestConnection
	 */
	final protected function getConnection()
	{
		if (self::$connection === NULL) {
			self::$connection = new TestConnection($this->getDoctrineConnection());
		}

		return self::$connection;
	}



	/**
	 * @return Doctrine\DBAL\Connection
	 */
	final protected function getDoctrineConnection()
	{
		return $this->getEntityManager()->getConnection();
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	final protected function getEntityManager()
	{
		if (self::$em === NULL) {
			self::$em = $this->createEntityManager();
		}

		return self::$em;
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	protected function createEntityManager()
	{
		if (self::$doctrineContainer === NULL) {
			self::$doctrineContainer = $container = new Kdyby\Doctrine\ORM\Container($this->getContext(), array(
				'driver' => 'pdo_sqlite',
				//'dsn' => 'sqlite::memory:',
				'memory' => TRUE
			));

			$em = $container->getEntityManager();

			// prepare schema
			$classes = $em->getMetadataFactory()->getAllMetadata();
			$container->schemaTool->dropDatabase();
			$container->schemaTool->createSchema($classes);

			// register automatic fixtures loading
			$em->getEventManager()->addEventSubscriber($container->dataFixturesListener);
		}

		return self::$doctrineContainer->getEntityManager();
	}



	/**
	 * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
		$databaseMeta = $this->getConnection()->getMetaData();
		foreach ($databaseMeta->getTableNames() as $tableName) {
			$metadata = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
					$tableName,
					$databaseMeta->getTableColumns($tableName),
					$databaseMeta->getTablePrimaryKeys($tableName)
				);

			$dataSet->addTable(new \PHPUnit_Extensions_Database_DataSet_DefaultTable($metadata));
		}

		return $dataSet;
	}



	/**
	 * Returns the database operation executed in test setup.
	 *
	 * @return \PHPUnit_Extensions_Database_Operation_DatabaseOperation
	 */
	protected function getSetUpOperation()
	{
		return new \PHPUnit_Extensions_Database_Operation_Composite(array(
			new DoctrineExtensions\PHPUnit\Operations\Truncate,
			//new \PHPUnit_Extensions_Database_Operation_Insert
		));
	}



	/**
	 * @param array $tableNames
	 * @return \PHPUnit_Extensions_Database_DataSet_QueryDataSet
	 */
	protected function createQueryDataSet(array $tableNames = NULL)
	{
		$dbTables = $this->getConnection()->getMetaData()->getTableNames();
		foreach ($tableNames as &$tableName) {
			if (in_array($tableName, $dbTables, TRUE)) {
				continue;
			}

			$tableName = $this->getTableName($tableName);
		}

		return $this->getConnection()->createDataSet($tableNames);
	}



	/**
	 * @param  string $tableName
	 * @param  string $sql
	 * @return \PHPUnit_Extensions_Database_DataSet_QueryTable
	 */
	protected function createQueryDataTable($tableName, $sql = NULL)
	{
		$dbTables = $this->getConnection()->getMetaData()->getTableNames();
		if (!in_array($tableName, $dbTables, TRUE)) {
			$tableName = $this->getTableName($tableName);
		}

		if ($sql == NULL) {
			$sql = 'SELECT * FROM ' . $tableName;
		}

		return $this->getConnection()->createQueryTable($tableName, $sql);
	}



	/**
	 * Creates a IDatabaseTester for this testCase.
	 *
	 * @return PHPUnit_Extensions_Database_ITester
	 */
	protected function newDatabaseTester()
	{
		return new DatabaseTester($this->getConnection());
	}


	/********************* EntityManager shortcuts *********************/



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


	/********************* Database DataSets *********************/



	/**
	 * @param string $file
	 * @return \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
	 */
	protected function createDataSet($file = NULL)
	{
		$extension = $file ? pathinfo($file, PATHINFO_EXTENSION) : NULL;
		if ($extension === 'neon') {
			return $this->createNeonDataSet($file);

		} elseif ($file !== NULL) {
			throw new Nette\NotImplementedException("Handling of filetype $extension is not implemented yet.");
		}

		$resolver = new DataSetFilenameResolver($this);
		return $this->createDataSet($resolver->resolve());
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