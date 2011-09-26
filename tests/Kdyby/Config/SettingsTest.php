<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Config;

use Doctrine;
use Kdyby;
use Kdyby\Config\Settings;
use Nette;
use Nette\Caching\Storages\MemoryStorage;



/**
 * @author Filip Procházka
 */
class SettingsTest extends Kdyby\Testing\OrmTestCase
{

	/** @var Settings */
	private $settings;

	/** @var Kdyby\Doctrine\ORM\Dao */
	private $dao;

	/** @var MemoryStorage */
	private $storage;



	public function setup()
	{
		parent::setup();

		$this->dao = $this->getDao('Kdyby\Config\Setting');
		$this->storage = new MemoryStorage();
		$this->settings = new Settings($this->dao, $this->storage);
	}



	public function testProvidesDao()
	{
		$this->assertInstanceOf('Kdyby\Doctrine\ORM\Dao', $this->settings->getDao());
		$this->assertSame($this->dao, $this->settings->getDao());
	}



	public function testSavingSettings()
	{
		$tableName = $this->getTableName('Kdyby\Config\Setting');
		$dataset = $this->createDataSet();

		foreach ($dataset->getTable($tableName) as $row) {
			$this->settings->set($row['name'], $row['value'], $row['section']);
		}

		$table = $this->createQueryDataTable('Kdyby\Config\Setting');
		$this->assertSame(5, $table->getRowCount());
		$this->assertTablesEqual($dataset->getTable($tableName), $table);
	}



	/**
	 * @Fixture("SettingsData")
	 */
	public function testDeletingSettings()
	{
		$table = $this->createQueryDataTable('Kdyby\Config\Setting');
		$this->assertSame(5, $table->getRowCount());

		$this->settings->delete(NULL, 'database');
		$this->settings->delete('imageDir');

		$table = $this->createQueryDataTable('Kdyby\Config\Setting');
		$this->assertSame(0, $table->getRowCount());
	}

}