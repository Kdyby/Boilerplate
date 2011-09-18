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
use Kdyby\Doctrine\ORM\EntityRepository;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;



/**
 * @author Filip Procházka
 *
 * @property-read EntityRepository $repository
 */
class Settings extends Nette\Object
{

	const CACHE_NAMESPACE = 'Kdyby.Configurator';

	/** @var EntityRepository */
	private $repository;

	/** @var Cache */
	private $cache;



	/**
	 * @param EntityRepository $entityManager
	 * @param IStorage|NULL $storage
	 */
	public function __construct(EntityRepository $repository, IStorage $storage = NULL)
	{
		$this->repository = $repository;

		if ($storage) {
			$this->cache = new Cache($storage, self::CACHE_NAMESPACE);
		}
	}



	/**
	 * @return EntityRepository
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
		$setting = $this->getRepository()->fetchOne(new SettingQuery($name, $section));
		if ($setting === NULL) {
			$setting = new Setting($name, $section);
		}

		$setting->setValue($value);
		$this->getRepository()->save($setting);
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
		$setting = $this->getRepository()->findOneByNameAndSection($name, $section);
		if ($setting == NULL) {
			return;
		}

		$this->getRepository()->delete($setting);
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
			$setting->apply($container->params);
		}
	}

}