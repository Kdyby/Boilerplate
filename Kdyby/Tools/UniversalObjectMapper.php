<?php

namespace Kdyby\Tools;

use Kdyby;
use Nette;



class UniversalObjectMapper extends Nette\Object
{

	/** @var string */
	private $className;

	/** @var Nette\Reflection\ClassReflection */
	private $classRef;

	/** @var array */
	private $propertiesRef;

	/** @var object */
	private $prototype;

	/** @var array */
	private $columns = array();

	/** @var array */
	private $prefix = array();

	/** @var array */
	private $columnsMap = array();



	/**
	 * @param string $className
	 */
	public function __construct($className)
	{
		if (!is_string($className)) {
			throw new \InvalidArgumentException("Class name must be string.");
		}

		if (!class_exists($className)) {
			throw new \InvalidArgumentException("Class " . $className . " does not exists.");
		}

		$this->className = $className;
		$this->classRef = new Nette\Reflection\ClassReflection($className);
	}



	/**
	 * @param array $columns
	 * @param string $prefix
	 * @return UniversalObjectMapper
	 */
	public function setColumns(array $columns)
	{
		$this->columns = $columns;
		return $this;
	}



	/**
	 * @param string $tableName
	 * @return array
	 */
	public function getColumns($tableName = NULL, $glue = " AS ")
	{
		$prefix = $this->prefix ?: NULL;

		return array_map(function ($column) use ($tableName, $glue, $prefix) {
			return ($tableName ? $tableName . "." . $column . ($glue ?: ' ') : NULL) . $prefix . $column;
		}, $this->columns);
	}



	/**
	 * Keys are property names
	 * Values are column names
	 *
	 * @param array $map
	 * @return UniversalObjectMapper
	 */
	public function setColumnsMap(array $map)
	{
		$diff = array_diff(array_keys($map), array_keys($this->getProperties(TRUE)));
		if ($diff) {
			throw new \InvalidArgumentException("Following properties are not defined in class " . $this->className . ": " . implode(', ', $diff));
		}

		$this->columnsMap = $map;
		return $this;
	}



	/**
	 * @return array
	 */
	public function getColumnsMap()
	{
		return $this->getPropertiesToColumns();
	}



	/**
	 * @param string $prefix
	 * @return UniversalObjectMapper
	 */
	public function setPrefix($prefix)
	{
		if (!is_string($prefix) && $prefix !== NULL) {
			throw new \InvalidArgumentException("Prefix must be string, " . gettype($prefix) . " given.");
		}

		$this->prefix = $prefix;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}



	/**
	 * @param array $arguments
	 * @param array $data
	 * @param bool $useColumnMapping
	 * @return object
	 */
	public function createNew(array $arguments = array(), array $data = array(), $useColumnMapping = FALSE)
	{
		return $this->load($this->classRef->newInstanceArgs($arguments), $data, $useColumnMapping);
	}



	/**
	 * @param array $data
	 * @param bool $useColumnMapping
	 * @return object
	 */
	public function restore(array $data = array(), $useColumnMapping = FALSE)
	{
		if ($this->prototype === NULL) {
			$this->prototype = unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->className), $this->className));

			if ($this->prototype === FALSE) {
				throw new \InvalidStateException("Can't create new object of " . $this->className);
			}
		}

		return $this->load(clone $this->prototype, $data, $useColumnMapping);
	}



	/**
	 * @param className $object
	 * @param array $data
	 * @param bool $useColumnMapping
	 * @return object
	 */
	public function load($object, array $data, $useColumnMapping = FALSE)
	{
		if (!$object instanceof $this->className) {
			throw new \InvalidArgumentException("Given object is not instance of " . $this->className . ".");
		}

		if (!$data) {
			return $object;
		}

		// translate columns to property names
		if ($useColumnMapping) {
			$tmp = array();
			foreach (array_intersect_key(array_flip($this->getPropertiesToColumns()), $data) as $column => $property) {
				$tmp[$property] = $data[$column];
				unset($data[$column]);
			}

			$data = $tmp;
		}

		// get reflecton of properties
		foreach (array_intersect_key($this->getProperties(), $data) as $property => $propRef) {
			$propRef->setValue($object, $data[$property]);
		}

		return $object;
	}



	/**
	 * @param object $object
	 * @param bool $useColumnMapping
	 * @return array
	 */
	public function save($object, $useColumnMapping = FALSE)
	{
		if (!$object instanceof $this->className) {
			throw new \InvalidArgumentException("Given object is not instance of " . $this->className . ".");
		}

		$data = array();
		foreach ($this->getProperties() as $property => $propRef) {
			$data[$property] = $propRef->getValue($object);
		}

		// translate columns to property names
		if ($useColumnMapping) {
			$tmp = array();
			foreach ($this->columnsMap as $property => $column) {
				$tmp[$column] = $data[$property];
				unset($data[$column]);
			}
			return $tmp;
		}

		return $data;
	}



	/**
	 * @param bool $returnAll
	 * @return array
	 */
	private function getProperties($all = FALSE)
	{
		if ($this->propertiesRef === NULL) {
			foreach ($this->classRef->getProperties() as $property) {
				$property->setAccessible(TRUE);
				$this->propertiesRef[$property->getName()] = $property;
			}
		}

		if ($all === FALSE && $this->columnsMap) {
			return array_intersect_key($this->propertiesRef, $this->columnsMap);
		}

		return $this->propertiesRef;
	}



	/**
	 * @return array
	 */
	private function getPropertiesToColumns()
	{
		$prefix = $this->prefix ?: NULL;

		return array_map(function ($column) use ($prefix) {
			return $prefix . $column;
		}, $this->columnsMap);
	}

}