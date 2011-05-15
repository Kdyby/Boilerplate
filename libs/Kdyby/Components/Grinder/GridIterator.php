<?php

namespace Kdyby\Components\Grinder;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class GridIterator extends \IteratorIterator
{

	/** @var Grid */
	private $grid;

	/** @var Models\IModel */
	private $model;



	/**
	 * @param Grid $grid
	 * @param Models\IModel $model
	 */
	public function __construct(Grid $grid, Models\IModel $model)
	{
		$this->grid = $grid;
		$this->model = $model;

		parent::__construct(new \ArrayIterator($model->getItems()), NULL);
	}



	/**
	 * Return the current element
	 * 
	 * @return mixed Can return any type.
	 */
	public function current()
	{
		$record = parent::current();
		$this->grid->bindRecord($this->key(), $record);
		return $record;
	}



	/**
	 * @return int|string
	 */
	public function getCurrentUniqueId()
	{
		return $this->grid->getModel()->getUniqueId($this->current());
	}

}