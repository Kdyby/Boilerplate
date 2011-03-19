<?php

namespace Gridito;

use DibiFluent;

/**
 * DibiFluent model
 *
 * @author Jan Marek
 * @license MIT
 */
class DibiFluentModel extends AbstractModel
{
	/** @var DibiFluent */
	protected $fluent;

	/** @var string */
	protected $rowClass;



	/**
	 * Constructor
	 * @param DibiFluent dibi fluent object
	 * @param string row class name
	 */
	public function __construct(DibiFluent $fluent, $rowClass = "DibiRow")
	{
		$this->fluent = $fluent;
		$this->rowClass = $rowClass;
	}



	public function getItemByUniqueId($uniqueId)
	{
		$fluent = clone $this->fluent;
		$fluent->where("%n = %i", $this->getPrimaryKey(), $uniqueId);
		return $fluent->execute()->setRowClass($this->rowClass)->fetch();
	}



	public function getItems()
	{
		$fluent = clone $this->fluent;

		$fluent->limit($this->getLimit());
		$fluent->offset($this->getOffset());

		list($sortColumn, $sortType) = $this->getSorting();
		if ($sortColumn) {
			$fluent->orderBy("[$sortColumn] $sortType");
		}

		return $fluent->execute()->setRowClass($this->rowClass)->fetchAll();
	}



	/**
	 * Item count
	 * @return int
	 */
	protected function _count()
	{
		return $this->fluent->count();
	}

}