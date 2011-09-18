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
class NeonDataSet extends \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{

	/** @var array */
	protected $tables = array();

	/** @var \PHPUnit_Extensions_Database_DB_IMetaData */
	private $databaseMetadata;



	/**
	 * Creates a new Neon dataset
	 *
	 * @param \PHPUnit_Extensions_Database_DB_IMetaData $metadata
	 * @param string $neonFile
	 */
	public function __construct(\PHPUnit_Extensions_Database_DB_IMetaData $metadata, $neonFile)
	{
		$this->databaseMetadata = $metadata;
		$this->addNeonFile($neonFile);
	}



	/**
	 * Adds a new Neon file to the dataset.
	 * @param string $neonFile
	 */
	public function addNeonFile($neonFile)
	{
		$data = Neon::decode(file_get_contents($neonFile));

		foreach ($data as $tableName => $rows) {
			if (!isset($rows)) {
				$rows = array();
			}

			if (!is_array($rows)) {
				continue;
			}

			if (!array_key_exists($tableName, $this->tables)) {
				$this->addTable($tableName);
			}

			$this->tables[$tableName]->addRows($rows);
		}
	}



	/**
	 * @param string $tableName
	 */
	private function addTable($tableName)
	{
		$tableMetaData = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
			$tableName,
			$this->databaseMetadata->getTableColumns($tableName),
			$this->databaseMetadata->getTablePrimaryKeys($tableName)
		);

		return $this->tables[$tableName] = new DataSetTable($tableMetaData);
	}



	/**
	 * Creates an iterator over the tables in the data set. If $reverse is
	 * true a reverse iterator will be returned.
	 *
	 * @param bool $reverse
	 * @return \PHPUnit_Extensions_Database_DataSet_ITableIterator
	 */
	protected function createIterator($reverse = FALSE)
	{
		return new \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator(
			$this->tables, $reverse
		);
	}



	/**
	 * Saves a given $dataset to $filename in Neon format
	 * @param \PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
	 * @param string $filename
	 */
	public static function write(\PHPUnit_Extensions_Database_DataSet_IDataSet $dataset, $filename)
	{
		$pers = new NeonDataSetPersistor();
		$pers->setFileName($filename);

		try {
			$pers->write($dataset);

		} catch (\RuntimeException $e) {
			throw new \PHPUnit_Framework_Exception(__METHOD__ . ' called with an unwritable file.');
		}
	}

}
