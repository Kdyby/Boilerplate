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

	/** @var Kdyby\Doctrine\ORM\EntityRepository */
	private $repository;

	/** @var MemoryStorage */
	private $storage;



	public function setup()
	{
		parent::setup();

		$this->repository = $this->getRepository('Kdyby\Config\Setting');
		$this->storage = new MemoryStorage();
		$this->settings = new Settings($this->repository, $this->storage);
	}



	public function testProvidesRepository()
	{
		$this->assertInstanceOf('Kdyby\Doctrine\ORM\EntityRepository', $this->settings->getRepository());
		$this->assertSame($this->repository, $this->settings->getRepository());
	}



	public function testSavingSettings()
	{
		$tableName = $this->getTableName('Kdyby\Config\Setting');
		$dataset = $this->createDataSet();

		foreach ($dataset->getTable($tableName) as $row) {
			$this->settings->set($row['name'], $row['value'], $row['section']);
		}

		$table = $this->createQueryDataTable($tableName);
		$this->assertSame(5, $table->getRowCount());
		$this->assertTablesEqual($dataset->getTable($tableName), $table);
	}



	/**
	 * @Fixture("SettingsData")
	 */
	public function testDeletingSettings()
	{
		$this->settings->delete(NULL, 'database');
		$this->settings->delete('imageDir');

		$table = $this->createQueryDataTable($this->getTableName('Kdyby\Config\Setting'));
		$this->assertSame(0, $table->getRowCount());
	}

}