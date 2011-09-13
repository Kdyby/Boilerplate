<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Doctrine\ORM\EntityManager;
use Kdyby;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;



/**
 * @author Filip Procházka
 *
 * @property-read SettingsRepository $repository
 */
class Settings extends Nette\Object
{

	const CACHE_NAMESPACE = 'Kdyby.Configurator';

	/** @var SettingsRepository */
	private $repository;

	/** @var Cache */
	private $cache;



	/**
	 * @param SettingsRepository $entityManager
	 * @param IStorage|NULL $storage
	 */
	public function __construct(SettingsRepository $repository, IStorage $storage = NULL)
	{
		$this->repository = $repository;

		if ($storage) {
			$this->cache = new Cache($storage, self::CACHE_NAMESPACE);
		}
	}



	/**
	 * @return SettingsRepository
	 */
	public function getRepository()
	{
		return $this->repository;
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 * @param string $section
	 */
	public function set($name, $value, $section = NULL)
	{
		$setting = $this->getRepository()->findOneByNameAndSection($name, $section);
		if ($setting === NULL) {
			$setting = new Setting($name, $section);
		}

		$setting->setValue($value);
		$this->getRepository()->save($setting);
	}



	/**
	 * @param string $name
	 * @param string $section
	 */
	public function delete($name, $section = NULL)
	{
		$setting = $this->getRepository()->findOneByNameAndSection($name, $section);
		if ($setting == NULL) {
			return;
		}

		$this->getRepository()->delete($setting);
	}



	/**
	 * @param Kdyby\DI\Container $container
	 */
	public function loadAll(Kdyby\DI\Container $container)
	{
		if ($this->cache && is_array($this->cache->load('settings'))) {
			$settings = $this->cache->load('settings');

		} else {
			$settings = $this->repository->findAll();

			if ($this->cache) {
				$this->cache->save('settings', $settings, array(
					Cache::TAGS => array('settings'),
					Cache::EXPIRE => '+1 hour',
					Cache::SLIDING => TRUE
				));
			}
		}

		foreach ($settings as $setting) {
			if (!$setting->section) {
				$container->params[$setting->name] = $setting->value;
				continue;
			}

			$container->params[$setting->section][$setting->name] = $setting->value;
		}
	}

}