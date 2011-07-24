<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Models;

use ArrayIterator;
use Nette;



/**
 * Abstract Grinder model
 *
 * @author Jan Marek
 * @license MIT
 */
abstract class AbstractModel extends Nette\Object
{
	/** @var int */
	private $limit;

	/** @var int */
	private $offset;

	/** @var array */
	private $sorting = array(NULL, NULL);

	/** @var string */
	private $primaryKey = "id";

	/** @var int */
	private $count = NULL;



	/**
	 * @return int
	 */
	abstract protected function doCount();



	/**
	 * @param int $limit
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
	}



	/**
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}



	/**
	 * @return int
	 */
	public function getOffset()
	{
		return $this->offset;
	}



	/**
	 * @param int $offset
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;
	}



	/**
	 * @param string column
	 * @param string asc or desc
	 */
	public function applySorting($column, $type)
	{
		return $this->sorting = array($column, $type);
	}



	/**
	 * @return array
	 */
	protected function getSorting()
	{
		return $this->sorting;
	}



	/**
	 * @param string $name
	 */
	public function setPrimaryKey($name)
	{
		$this->primaryKey = $name;
	}



	/**
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}



	/**
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getItems());
	}



	/**
	 * @param object $item
	 * @return mixed
	 */
	public function getUniqueId($item)
	{
		return $item->{$this->getPrimaryKey()};
	}



	/**
	 * @param array $uniqueIds
	 * @return array
	 */
	public function getItemsByUniqueIds(array $uniqueIds)
	{
		return array_map(array($this, "getItemByUniqueId"), $uniqueIds);
	}



	/**
	 * @return int
	 */
	public function count()
	{
		if ($this->count === null) {
			$this->count = $this->doCount();
		}

		return $this->count;
	}

}