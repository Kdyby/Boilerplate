<?php

namespace Kdyby\Tools;

use Kdyby;
use Nette;



class UniversalObjectMapper extends Nette\Object
{

	/** @var string */
	private $className;

	/** @var string */
	private $classRef;

	/** @var object */
	private $prototype;



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
	 * @param array $data
	 * @return object
	 */
	public function createNew(array $data = array())
	{
		if ($this->prototype === NULL) {
			$this->prototype = unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->className), $this->className));

			if ($this->prototype === FALSE) {
				throw new \InvalidStateException("Can't create new object of " . $this->className);
			}
		}

		return $this->load(clone $this->prototype, $data);
	}



	/**
	 * @param object $object
	 * @param array $data
	 * @return object
	 */
	public function load($object, $data)
	{
		if (!$object instanceof $this->className) {
			throw new \InvalidArgumentException("Given object is not instance of " . $this->className . ".");
		}

		$prepared = array();
		foreach ($data as $property => $value) {
			if (!$this->classRef->hasProperty($property)) {
				throw new \MemberAccessException("Can't set value of non-existing property " . $property . ".");
			}

			$propRef = $this->classRef->getProperty($property);
			$propRef->setAccessible(TRUE);
			$prepared[] = array($propRef, $value);
		}

		foreach ($prepared as $propertyInfo) {
			$propertyInfo[0]->setValue($object, $propertyInfo[1]);
		}

		return $object;
	}



	/**
	 * @param object $object
	 */
	public function save($object)
	{
		if (!$object instanceof $this->className) {
			throw new \InvalidArgumentException("Given object is not instance of " . $this->className . ".");
		}

		$date = array();
		foreach ($this->classRef->getProperties() as $propRef) {
			$propRef->setAccessible(TRUE);
			$date[$propRef->getName()] = $propRef->getValue($object);
		}

		return $date;
	}

}