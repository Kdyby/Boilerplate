<?php

namespace Kdyby\Model;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadata;
use Nette;
use Kdyby;



/**
 * @property array $fields
 * @property array $sets
 * @property-read ClassMetadata $meta
 */
final class EntityFields extends Nette\FreezableObject
{

	/** @var string */
	private $entityMetadata;

	/** @var array */
	private $fields;

	/** @var array */
	private $sets;



	/**
	 * @param ClassMetadata $entityMetadata
	 * @param array $fields
	 * @param array $sets
	 */
	public function __construct(ClassMetadata $entityMetadata, array $fields = array(), array $sets = array())
	{
		$this->entityMetadata = $entityMetadata;

		$this->addFields($fields);
		$this->addSets($sets);
	}



	/**
	 * @return string
	 */
	public function getEntityName()
	{
		return $this->entityFields->meta->rootEntityName;
	}



	/**
	 * @return ClassMetadata
	 */
	public function getMeta()
	{
		return $this->entityMetadata;
	}



	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}



	/**
	 * @param array $fields
	 * @throws InvalidStateException
	 * @return EntityFields
	 */
	public function addFields(array $fields)
	{
		$this->updating();

		$fields = array();

		foreach ($fields as $field) {
			$this->needField($field);
			$fields[] = $field;
		}

		$this->fields = $fields;

		return $this; // fluent
	}



	/**
	 * @param string $field
	 * @throws InvalidStateException
	 * @return EntityFields
	 */
	public function addField($field)
	{
		$this->updating();

		$this->needField($field);
		$this->fields[] = $field;

		return $this; // fluent
	}



	/**
	 * @param string $name
	 * @param array $fields
	 * @return EntityFields
	 */
	public function addSet($name, array $fields)
	{
		$this->updating();

		foreach ($fields as $field) {
			if (!in_array($field, $this->fields, TRUE)) {
				$this->addField($field);
			}
		}

		$this->needFields($fields);
		$this->sets[$name] = $fields;

		return $this; // fluent
	}



	/**
	 * @param string $name
	 * @throws InvalidStateException
	 * @return array
	 */
	public function getSet($name)
	{
		if (!isset($this->sets[$name])) {
			throw new Nette\InvalidStateException("Fieldset with name " . $name . " is not defined.");
		}

		return $this->sets[$name];
	}



	/**
	 * @param array $sets
	 * @throws InvalidStateException
	 * @return EntityFields
	 */
	public function addSets(array $sets)
	{
		$this->updating();

		foreach ($sets as $name => $fields) {
			$this->addSet($name, $fields);
		}

		return $this;
	}



	/**
	 * @return array
	 */
	public function getSets()
	{
		return $this->sets;
	}



	/**
	 * @param string $field
	 * @throws \InvalidArgumentException
	 */
	private function needField($field)
	{
		if (!in_array($field, $this->entityMetadata->fieldNames, TRUE)) {
			throw new \InvalidArgumentException("Entity " . $this->getEntityName() . " has no field " . $field . ".");
		}
	}



	/**
	 * @param array $fields
	 * @throws \InvalidArgumentException
	 */
	private function needFields(array $fields)
	{
		foreach ($fields as $field) {
			$this->needField($field);
		}
	}

}