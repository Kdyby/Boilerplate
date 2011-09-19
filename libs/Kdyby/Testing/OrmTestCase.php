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
	 * Initialize DB connection and load Data Fixtures
	 */
	protected function setup()
	{
		parent::setup();
		$this->loadFixtures();
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


	/********************* Data Fixtures *********************/



	/**
	 * Appends Data Fixtures to current database DataSet
	 */
	private function loadFixtures()
	{
		$loader = self::$doctrineContainer->fixturesLoader;
		foreach ($this->getTestFixtureClasses() as $class) {
			$loader->addFixture(new $class);
		}

		self::$doctrineContainer->fixturesExecutor
			->execute($loader->getFixtures(), TRUE);
	}



	/**
	 * @return array
	 */
	private function getTestFixtureClasses()
	{
		$annotations = $this->getReflection()
			->getMethod($this->getName(FALSE))->getAnnotations();

		return array_map(function ($class) use ($method) {
			if (substr_count($class, '\\') !== 0) {
				return $class;
			}

			return $method->getDeclaringClass()->getNamespaceName() . '\\' .  $class;
		}, isset($annotations['Fixture']) ? $annotations['Fixture'] : array());
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

		return $this->createDataSet($this->resolveTestDataSetFilename());
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



	/**
	 * @return string
	 */
	private function resolveTestDataSetFilename()
	{
		$filenamePart = $this->getTestDirectory() . DIRECTORY_SEPARATOR .
				$this->getTestCaseName() . '.' . $this->getTestName();

		foreach (array('xml', 'yaml', 'csv', 'neon') as $extension) {
			if (file_exists($file = $filenamePart . '.' . $extension)) {
				return $file;
			}
		}

		throw new Nette\IOException("File '" . $file . "' not found.");
	}



	/**
	 * @return string
	 */
	private function getTestDirectory()
	{
		$class = $this->getReflection()
			->getMethod($this->getName(FALSE))->getDeclaringClass();

		return dirname($class->getFileName());
	}



	/**
	 * @return string
	 */
	private function getTestCaseName()
	{
		$className = get_class($this);
		return str_replace('Test', '', substr($className, strrpos($className, '\\') + 1));
	}



	/**
	 * @return string
	 */
	private function getTestName()
	{
		return lcFirst(str_replace('test', '', $this->getName(FALSE)));
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