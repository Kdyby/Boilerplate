<?php

namespace Kdyby\Model\Finders\Filters;

use Nette;
use Kdyby;



/**
 * The actual filter logic element
 *
 * @todo create properties for setting if filter is on or off and control by $this->isIndependent() returned by handleChangeState()
 *
 * @property-read string $name
 * @property-read bool $independent
 * @property-read array $fields
 * @property array $options
 */
class ResultFilter extends Nette\Object implements IFinderFilter
{

	/** @var string */
	private $name;

	/** @var Method\IResultFilterMethod */
	private $method;

	/** @var array */
	private $fields = array();

	/** @var array */
	private $values = array();

	/** @var array */
	private $options = array();

	/** @var bool */
	private $independent = FALSE;



	/**
	 * @param array $fields
	 */
	public function __construct($name, Method\IResultFilterMethod $method, array $fields = array())
	{
		$this->name = $name;
		$this->method = $method;
		$this->fields = $fields;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param bool $independent
	 */
	public function setIndependent($independent = TRUE)
	{
		$this->independent = (bool)$independent;
	}



	/**
	 * @return bool
	 */
	public function isIndependent()
	{
		return $this->independent;
	}



	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}



	/**
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		if (count($this->fields) > 1) {
			throw new Nette\InvalidStateException("Cannot use options, when managing multiple fields.");
		}

		$this->options = $options;
	}



	/**
	 * @todo define method for checking existenc of options?
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}



	/**
	 * @todo throw away when not in options, or scream?
	 * @param array|string $state
	 */
	public function loadState($state)
	{
		if (count($this->fields) <= 1) {
			if (is_array($state)) {
				return; // TODO: throw exception?
			}

			if ($this->options && !in_array($state, $this->options)) {
				return; // TODO: throw exception?
			}

			$this->values[count($this->fields) ? current($this->fields) : NULL] = $state;
			$state = NULL;

		} else {
			foreach ($this->fields as $field) {
				$this->values[$field] = isset($state[$field]) ? $state[$field] : NULL;
				unset($state[$field]);
			}
		}

		if ($state) {
			throw new Nette\InvalidStateException("Filter recieved too many values. Following values are redundant: " . implode(', ', array_keys($values)));
		}
	}



	/**
	 * @return array|string
	 */
	public function saveState()
	{
		if (count($this->fields) <= 1) {
			return current((array)$this->values); // simplifies storage of filters state
		}

		$state = array();

		foreach ($this->fields as $field) {
			if ($this->values[$field] !== NULL)
				$state[$field] = $this->values[$field];
		}

		return $state;
	}



	/**
	 * @param array|string $newState
	 * @return bool
	 */
	public function handleChangeState($newState)
	{
		if ($name && !in_array($name, $this->fields, TRUE)) {
			throw new Nette\InvalidStateException("Field " . $name . " is not managed by this filter.");
		}

		$this->loadState($state);
		return $this->isIndependent();
	}



	/**
	 * @return Doctrine\ORM\Query\Expr
	 */
	public function buildFragment()
	{
		return $this->method->buildFragment($this->values, $this->fields) ?: NULL;
	}

}