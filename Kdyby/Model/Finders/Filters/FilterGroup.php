<?php

namespace Kdyby\Model\Finders\Filters;

use Doctrine;
use Doctrine\ORM\Query\Expr;
use Nette;
use Kdyby;



/**
 * Filters group (for visual separation)
 *
 * Will implement specific methods for adding filters
 *
 * @property string $name
 * @property bool $independent
 */
class FilterGroup extends Nette\Object implements IFinderFilter
{

	/** @var string */
	private $name;

	/** @var bool */
	private $independent = FALSE;

	/** @var array */
	private $filters;



	/**
	 * @param string $name
	 * @param bool $independent
	 */
	public function __construct($name)
	{
		$this->name = $name;
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
	 * @param string $name
	 * @param array $fields
	 * @param string $methodName
	 * @return ResultFilter
	 */
	function addFilter($name, array $fields, $methodName)
	{
		if (isset($this->filters[$name])) {
			throw new Nette\InvalidStateException("ResultFilter with name " . $name . " is already defined, in group " . $this->getName() . '.');
		}

		return $this->filters[$name] = new ResultFilter($name, $this->getMethod($methodName), $fields);
	}



	/**
	 * @return array
	 */
	function getFilters()
	{
		return $this->filters;
	}



	/**
	 * @param array $state
	 */
	public function loadState($state)
	{
		foreach ($this->getFilters() as $name => $filter) {
			$filter->loadState(isset($state[$name]) ? $state[$name] : NULL);
		}
	}



	/**
	 * @return array
	 */
	public function saveState()
	{
		$state = array();

		foreach ($this->getFilters() as $name => $filter) {
			$state[$name] = $filter->saveState();
		}

		return $state;
	}



	/**
	 * @param array $newState
	 * @return bool
	 */
	public function handleChangeState(array $newState)
	{
		foreach ($this->getFilters() as $name => $filter) {
			$filter->handleChangeState(isset($newState[$name]) ? $newState[$name] : NULL);
		}

		return $this->isIndependent();
	}



	/**
	 * @return Expr
	 */
	public function buildFragment()
	{
		$cond = new Expr\Andx();

		foreach ($this->filters as $filter) {
			$cond->add($filter->buildFragment());
		}

		return $cond;
	}



	/**
	 * @param string $methodName
	 * @return Method\IResultFilterMethod
	 */
	private function getMethod($methodName)
	{
		// todo: better!
		return ResultFilterMethodRegister::getMethod($methodName);
	}

}