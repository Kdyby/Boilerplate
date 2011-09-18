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
use Nette\Utils\Neon;



/**
 * @author Filip Procházka
 */
class NeonDataSetPersistor implements \PHPUnit_Extensions_Database_DataSet_IPersistable
{
	/** @var string */
	protected $filename;



	/**
	 * Sets the filename that this persistor will save to.
	 *
	 * @param string $filename
	 */
	public function setFileName($filename)
	{
		$this->filename = $filename;
	}



	/**
	 * Writes the dataset to a yaml file
	 *
	 * @param \PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
	 */
	public function write(\PHPUnit_Extensions_Database_DataSet_IDataSet $dataset)
	{
		$rows = array();
		$emptyTables = array();

		foreach ($dataset as $table) {
			$tableName = $table->getTableMetaData()->getTableName();
			$rowCount = $table->getRowCount();

			$rows[$tableName] = array();
			for ($i = 0; $i < $rowCount; $i++) {
				$rows[$tableName][] = $table->getRow($i);
			}
		}

		$data = Neon::encode($rows, Neon::BLOCK);
		if (!@file_put_contents($this->filename, $data)) {
			throw new Nette\IOException($this->filename . " is not writable");
		}
	}

}