<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class OrmTestCase extends TestCase
{

	/** @var \Kdyby\Tests\ORM\SandboxRegistry */
	private $ormSandbox;



	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	final protected function getEntityManager()
	{
		return $this->getDoctrine()->getEntityManager();
	}



	/**
	 * @return \Kdyby\Tests\ORM\SandboxRegistry
	 */
	final protected function getDoctrine()
	{
		if ($this->ormSandbox === NULL) {
			$this->createOrmSandbox();
		}

		return $this->ormSandbox;
	}



	/**
	 * @param array $entities
	 *
	 * @throws \Kdyby\InvalidStateException
	 */
	final protected function createOrmSandbox(array $entities = NULL)
	{
		if ($this->ormSandbox !== NULL) {
			throw new Kdyby\InvalidStateException("ORM Sandbox is already created for this test instance.");
		}

		$params = array(
			'wwwDir' => $this->getContext()->expand('%wwwDir%'),
			'appDir' => $this->getContext()->expand('%appDir%'),
			'tempDir' => $this->getContext()->expand('%tempDir%'),
			'container' => array('class' => 'ConsoleOrmContainer')
		);

		$config = new ORM\SandboxConfigurator($params);
		if (is_array($entities)) {
			$config->setEntities($entities);
		}

		$this->ormSandbox = $config->getRegistry();
		$this->ormSandbox->setCurrentTest($this);
		$this->ormSandbox->requireConfiguredManager();
	}


	/********************* EntityManager shortcuts *********************/


	/**
	 * @param string $entityName
	 * @return \Kdyby\Doctrine\Dao
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
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	protected function getMetadata($className)
	{
		if (is_object($className)) {
			$className = get_class($className);
		}

		return $this->getEntityManager()->getClassMetadata($className);
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

				} catch (\Exception $e) {
				}
			}

			$this->assertSame($value, $actualValue, "Property '" . $property . "' of '" . $entityName . "' equals given value.");
		}
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
			throw new Kdyby\NotImplementedException("Handling of file type $extension is not implemented yet.");
		}

		$resolver = new Tools\DataSetFilenameResolver($this);
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
