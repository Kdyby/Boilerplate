<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Config;

use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\Doctrine\ORM\Dao;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;



/**
 * @author Filip Procházka
 *
 * @property-read Dao $repository
 */
class Settings extends Nette\Object
{

	const CACHE_NAMESPACE = 'Kdyby.Configurator';

	/** @var Dao */
	private $dao;

	/** @var Cache */
	private $cache;



	/**
	 * @param Dao $dao
	 * @param IStorage|NULL $storage
	 */
	public function __construct(Dao $dao, IStorage $storage = NULL)
	{
		$this->dao = $dao;

		if ($storage) {
			$this->cache = new Cache($storage, self::CACHE_NAMESPACE);
		}
	}



	/**
	 * @return Dao
	 */
	public function getDao()
	{
		return $this->dao;
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 * @param string $section
	 */
	public function set($name, $value, $section = NULL)
	{
		$setting = $this->getDao()->fetchOne(new SettingQuery($name, $section));
		if ($setting === NULL) {
			$setting = new Setting($name, $section);
		}

		$setting->setValue($value);
		$this->getDao()->save($setting);
		$this->cache->clean(array(
				Cache::TAGS => array('settings'),
			));
	}



	/**
	 * @param string $name
	 * @param string $section
	 */
	public function delete($name, $section = NULL)
	{
		$query = new SettingQuery($name, $section);
		if ($name === NULL && $section !== NULL) {
			$setting = $this->getDao()->fetch($query);

		} else {
			$setting = $this->getDao()->fetchOne($query);
		}

		if ($setting == NULL) {
			return;
		}

		$this->getDao()->delete($setting);
		$this->cache->clean(array(
				Cache::TAGS => array('settings'),
			));
	}



	/**
	 * @param Kdyby\DI\Container $container
	 */
	public function loadAll(Kdyby\DI\Container $container)
	{
		if ($this->cache && is_array($this->cache->load('settings'))) {
			$settings = $this->cache->load('settings');

		} else {
			$settings = $this->getDao()->findAll();

			if ($this->cache) {
				$this->cache->save('settings', $settings, array(
					Cache::TAGS => array('settings'),
					Cache::EXPIRE => '+1 hour',
					Cache::SLIDING => TRUE
				));
			}
		}

		foreach ($settings as $setting) {
			$setting->apply($container->params);
		}
	}

}