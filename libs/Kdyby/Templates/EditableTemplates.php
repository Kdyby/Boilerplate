<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Templates;

use Kdyby;
use Kdyby\Caching\LatteStorage;
use Kdyby\Doctrine\Registry;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EditableTemplates extends Nette\Object
{

	const CACHE_NS = 'Kdyby.EditableTemplates';

	/**
	 * @var \Kdyby\Doctrine\Dao
	 */
	private $sourcesDao;

	/**
	 * @var \Nette\Caching\Cache
	 */
	private $cache;

	/**
	 * @var \Kdyby\Caching\LatteStorage
	 */
	private $storage;

	/**
	 * @var \Nette\Caching\IStorage
	 */
	private $cacheStorage;



	/**
	 * @param \Kdyby\Doctrine\Registry $doctrine
	 * @param \Kdyby\Caching\LatteStorage $storage
	 * @param \Nette\Caching\IStorage $cacheStorage
	 */
	public function __construct(Registry $doctrine, LatteStorage $storage, IStorage $cacheStorage)
	{
		$this->sourcesDao = $doctrine->getDao('Kdyby\Templates\TemplateSource');
		$this->cache = new Cache($this->storage = $storage, static::CACHE_NS);
		$this->cacheStorage = $cacheStorage;
	}



	/**
	 * @param TemplateSource $template
	 */
	public function refresh(TemplateSource $template)
	{
		$this->storage->clean(array(
			Cache::TAGS => array('dbTemplate#' . $template->getId())
		));
		$this->cacheStorage->clean(array(
			Cache::TAGS => array('dbTemplate#' . $template->getId())
		));
	}



	/**
	 * @param \Kdyby\Templates\TemplateSource $template
	 */
	public function save(TemplateSource $template)
	{
		$this->storage->hint = (string)$template->getId();
		static $trigger;
		if (!isset($trigger)) {
			$trigger = $template;
		}

		if ($extended = $template->getExtends()) {
			$this->save($extended);
		}

		$dp = array();
		if ($source = $template->build($this, $dp)) {
			$this->cache->save($template->getId(), $source, $dp);
		}

		if (isset($trigger) && $trigger === $template) {
			$this->sourcesDao->save($trigger);
			$trigger = NULL;
		}
	}



	/**
	 * @param \Kdyby\Templates\TemplateSource $template
	 */
	public function remove(TemplateSource $template)
	{
		$this->cache->clean(array(
			Cache::TAGS => array('dbTemplate#' . $template->getId())
		));
		$this->sourcesDao->delete($template);
	}



	/**
	 * @param \Kdyby\Templates\TemplateSource $template
	 * @param string $layoutFile
	 *
	 * @throws \Kdyby\InvalidStateException
	 * @throws \Kdyby\FileNotFoundException
	 * @return string
	 */
	public function getTemplateFile(TemplateSource $template, $layoutFile = NULL)
	{
		$this->storage->hint = (string)$template->getId();

		if (!$template->getId()) {
			$this->save($template);
		}

		$key = $template->getId();
		if ($layoutFile !== NULL) {
			$key .= '#l' . md5(serialize($layoutFile));
		}

		// load or save
		if (!$cached = $this->cache->load($key)) {
			$dp = array();
			$this->cache->save($key, $template->build($this, $dp, $layoutFile), $dp);
			$cached = $this->cache->load($key);
		}

		if ($cached === NULL) {
			throw new Kdyby\InvalidStateException("No template found.");

		} elseif (!file_exists($cached['file'])) {
			throw Kdyby\FileNotFoundException::fromFile($cached['file']);
		}

		@fclose($cached['handle']);
		return $cached['file'];
	}

}
