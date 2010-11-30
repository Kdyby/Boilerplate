<?php

namespace Kdyby\ORM;

use Kdyby;
use Nette;
use ORM;
use ORM\Criteria;



abstract class Query extends Nette\Object implements ORM\IQuery
{

	private $criteria = array();

	private $key;



	public static function create()
	{
		return new static;
	}



	public function &__get($name)
	{
		if ($this->key !== NULL) {
			throw new \InvalidStateException("Criteria was not defined for key '{$this->key}'");
		}
		$this->key = $name;
		return $this;
	}



	public function __set($property, $value)
	{
		$this->addCriteria(new Criteria($property, '=', $value));
	}



	public function __call($name, $args)
	{
		static $operators = array(
			'equals' => '=',
			'is' => '=',
			'lessThan' => '<',
			'lesserThan' => '<',
			'isLesserThan' => '<',
			'moreThan' => '>',
			'biggerThan' => '>',
			'isBiggerThan' => '>',
			'like' => 'like', // regexp support
			'match' => 'match', // regexp support
			'in' => 'in'
		);

		if (count($args) == 2 && $name == 'and') {
			if (!is_string($args[0])) {
				throw new \InvalidArgumentException("Key name expected as string, ".gettype($args[0])." given.");
			}

			if (!$args[1] instanceof self) {
				throw new \InvalidArgumentException("Second argument should be Query object.");
			}

			$this->criteria[$args[0]] = $args[1];
			return $this;
		}

		if (!isset($operators[$name])) {
			throw new \MemberAccessException();
		}
		if ($this->key === NULL) {
			throw new \InvalidStateException("Key was not specified for '$name' operator");
		}

		$this->addCriteria(new Criteria($this->key, $operators[$name], $args[0]));
		$this->key = NULL;

		return $this;
	}



	public function addCriteria(Criteria $criteria)
	{
		if (array_key_exists($criteria->key, $this->criteria)) {
			throw new \InvalidstateException("Criteria for '".$criteria->key."' already set!");
		}

		$this->criteria[$criteria->key] = $criteria;
	}



	public function getCriteria()
	{
		return $this->criteria;
	}
}
