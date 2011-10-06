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

	/** @var Kdyby\DI\Container */
	private $context;

	/** @var Kdyby\DI\Configurator */
	private $configurator;

	/** @var TempClassGenerator */
	private $tempClassGenerator;

	/** @var Database\MemoryDatabaseManager */
	private static $databaseManager;

	/** @var Kdyby\Doctrine\ORM\Container */
	private $doctrineContainer;

	/** @var Doctrine\ORM\EntityManager */
	private $em;

	/** @var DoctrineExtensions\PHPUnit\TestConnection */
	private $connection;



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
	 * @return Kdyby\DI\Container
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
		if ($this->connection === NULL) {
			$this->connection = new TestConnection($this->getDoctrineConnection());
		}

		return $this->connection;
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
		if ($this->em === NULL) {
			$this->em = $this->createEntityManager();
		}

		return $this->em;
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	protected function createEntityManager()
	{
		return $this->getDoctrineContainer()->getEntityManager();
	}



	/**
	 * @return Kdyby\Doctrine\ORM\Container
	 */
	protected function getDoctrineContainer()
	{
		if ($this->doctrineContainer === NULL) {
			$this->doctrineContainer = $this->getDatabaseManager()->refresh();
		}

		return $this->doctrineContainer;
	}



	/**
	 * @return Database\MemoryDatabaseManager
	 */
	private function getDatabaseManager()
	{
		if (self::$databaseManager === NULL) {
			self::$databaseManager = new Database\MemoryDatabaseManager($this->getContext());
		}

		return self::$databaseManager;
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
			if (is_object($tableName)) {
				$tableName = get_class($tableName);
			}

			if (in_array($tableName, $dbTables, TRUE)) {
				continue;
			}

			$meta = $this->getMetadata($tableName);
			foreach ($meta->getAssociationMappings() as $mapping) {
				if (!empty($mapping['joinTable'])) {
					$tableNames[] = $mapping['joinTable']['name'];
				}
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
		if (is_object($tableName)) {
			$tableName = get_class($tableName);
		}

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
	 * @return Doctrine\ORM\Dao
	 */
	protected function getDao($entityName)
	{
		if (is_object($entityName)) {
			$entityName = get_class($entityName);
		}

		return $this->getEntityManager()->getRepository($entityName);
	}



	/**
	 * @param string $className
	 * @return Doctrine\ORM\Mapping\ClassMetadata
	 */
	protected function getMetadata($className)
	{
		if (is_object($className)) {
			$className = get_class($className);
		}

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

		$resolver = new Database\DataSetFilenameResolver($this);
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


	/********************* TempClassGenerator *********************/


	/**
	 * @return TempClassGenerator
	 */
	private function getTempClassGenerator()
	{
		if ($this->tempClassGenerator === NULL) {
			$this->tempClassGenerator = new TempClassGenerator($this->getContext()->expand('%tempDir%/cache'));
		}

		return $this->tempClassGenerator;
	}



	/**
	 * @param string $class
	 * @return string
	 */
	protected function touchTempClass($class = NULL)
	{
		return $this->getTempClassGenerator()->generate($class);
	}



	/**
	 * @param string $class
	 * @return string
	 */
	protected function resolveTempClassFilename($class)
	{
		return $this->getTempClassGenerator()->resolveFilename($class);
	}


	/********************* Exceptions handling *********************/


	/**
	 * This method is called when a test method did not execute successfully.
	 *
	 * @param Exception $e
	 * @since Method available since Release 3.4.0
	 */
	protected function onNotSuccessfulTest(\Exception $e)
	{
		Nette\Diagnostics\Debugger::log($e);
		parent::onNotSuccessfulTest($e);
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