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
		$data = $this->createDataSet();
		$this->assertCount(5, $data);

		foreach ($data as $row) {
			$this->settings->set($row['name'], $row['value'], $row['section']);
		}

		$this->assertEntityCount(5, 'Kdyby\Config\Setting');

		$dao = $this->getDao('Kdyby\Config\Setting');
		foreach ($data as $row) {
			$this->assertEntityValues('Kdyby\Config\Setting', $row, $row['id']);
		}
	}



	/**
	 * @Fixture("SettingsData")
	 */
	public function testDeletingSettings()
	{
		$this->assertEntityCount(5, 'Kdyby\Config\Setting');
		$this->settings->delete(NULL, 'database');
		$this->assertEntityCount(1, 'Kdyby\Config\Setting');
		$this->settings->delete('imageDir');
		$this->assertEntityCount(0, 'Kdyby\Config\Setting');
	}

}