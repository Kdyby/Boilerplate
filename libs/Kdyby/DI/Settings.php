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
	 * @param string|NULL $section
	 * @param string|NULL $value
	 * @return Setting
	 */
	public function createNew($name, $section = NULL)
	{
		return new Setting($name, $section);
	}



	/**
	 * @param Setting $setting
	 * @return Setting
	 */
	public function save(Setting $setting)
	{
		$this->repository->save($setting);
		return $setting;
	}



	/**
	 * @param Setting $setting
	 */
	public function delete(Setting $setting)
	{
		$this->repository->delete($setting);
	}



	/**
	 * @param Kdyby\DI\Container $container
	 */
	public function loadAll(Kdyby\DI\Container $container)
	{
		if ($this->cache && $this->cache->load('settings')) {
			$settings = $this->cache->load('settings');

		} else {
			$settings = $this->repository->findAll();

			if ($this->cache) {
				$this->cache->save('settings', $settings);
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