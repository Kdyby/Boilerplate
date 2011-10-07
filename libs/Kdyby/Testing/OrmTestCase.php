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
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka
 */
abstract class OrmTestCase extends TestCase
{

	/** @var Db\ORM\MemoryDatabaseManager */
	private static $databaseManager;

	/** @var Kdyby\Doctrine\ORM\Container */
	private $doctrineContainer;

	/** @var Doctrine\ORM\EntityManager */
	private $em;



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	final protected function getEntityManager()
	{
		if ($this->em === NULL) {
			$this->em = $this->getDoctrineContainer()->getEntityManager();
			$this->em->getEventManager()->dispatchEvent('loadFixtures', new Db\ORM\EventArgs($this->em, $this));
		}

		return $this->em;
	}



	/**
	 * @return Kdyby\Doctrine\ORM\Container
	 */
	protected function getDoctrineContainer()
	{
		if ($this->doctrineContainer === NULL) {
			$this->doctrineContainer = $container = $this->getDatabaseManager()->refresh();
		}

		return $this->doctrineContainer;
	}



	/**
	 * @return Db\ORM\MemoryDatabaseManager
	 */
	private function getDatabaseManager()
	{
		if (self::$databaseManager === NULL) {
			self::$databaseManager = new Db\ORM\MemoryDatabaseManager($this->getContext());
		}

		return self::$databaseManager;
	}


	/********************* Asserts *********************/


	/**
	 * @param integer $expectedCount
	 * @param string|object $entityName
	 * @param string $message
	 */
	public function assertEntityCount($expectedCount, $entityName, $message = "")
	{
		$haystack = $this->getDao($entityName)
			->createQueryBuilder('e')
			->select('COUNT(e.id)')
			->getQuery()->getSingleScalarResult();

		$this->assertEquals($expectedCount, $haystack);
	}



	/**
	 * @param integer $values
	 * @param string|object $entityName
	 * @param string $message
	 */
	public function assertEntityValues($entityName, array $values, $id = NULL, $message = "")
	{
		$entityName = is_object($entityName) ? get_class($entityName) : $entityName;

		if ($id === NULL) {
			$result = $this->getDao($entityName)->findBy($values);

			$this->assertCount(1, $result);
			$entity = current($result);

		} else {
			$entity = $this->getDao($entityName)->find($id);
			$this->assertInstanceOf($entityName, $entity);
		}

		$meta = $this->getMetadata($entityName);
		foreach ($values as $property => $value) {
			$actualValue = $meta->getFieldValue($entity, $property);
			if ($actualValue instanceof Doctrine\Common\Collections\Collection) {
				$actualValue = $actualValue->toArray();

			} elseif (is_object($actualValue)) {
				try {
					$relationMeta = $this->getMetadata($actualValue);
					$actualValue = $relationMeta->getIdentifierValues($actualValue);
					if (count($actualValue) == 1) {
						$actualValue = current($actualValue);
					}

				} catch (\Exception $e) { }
			}

			$this->assertSame($value, $actualValue);
		}
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

		$resolver = new Db\DataSetFilenameResolver($this);
		return $this->createDataSet($resolver->resolve());
	}



	/**
	 * @param string $neonFile
	 * @return array
	 */
	protected function createNeonDataSet($neonFile)
	{
		return Nette\Utils\Neon::decode(file_get_contents($neonFile));
	}

}