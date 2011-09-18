<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Database;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class DataSetTable extends \PHPUnit_Extensions_Database_DataSet_DefaultTable implements \IteratorAggregate
{

	/**
	 * @param array $rows
	 */
	public function addRows(array $rows)
	{
		foreach ($rows as $row) {
			$this->addRow($row);
		}
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}

}