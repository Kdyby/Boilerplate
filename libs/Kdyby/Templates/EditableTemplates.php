<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
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
 * @author Filip Procházka <filip@prochazka.su>
 */
class EditableTemplates extends Nette\Object
{

	const CACHE_NS = "Kdyby.EditableTemplates";

	/**
	 * @var \Kdyby\Doctrine\Dao
	 */
	private $sourcesDao;

	/**
	 * @var \Kdyby\Caching\LatteStorage
	 */
	private $latteStorage;

	/**
	 * @var \Nette\Caching\IStorage
	 */
	private $cacheStorage;



	/**
	 * @param \Kdyby\Doctrine\Registry $doctrine
	 * @param \Kdyby\Caching\LatteStorage $latteStorage
	 * @param \Nette\Caching\IStorage $cacheStorage
	 */
	public function __construct(Registry $doctrine, LatteStorage $latteStorage, IStorage $cacheStorage = NULL)
	{
		$this->sourcesDao = $doctrine->getDao('Kdyby\Templates\TemplateSource');
		$this->latteStorage = $latteStorage;
		$this->cacheStorage = $cacheStorage;
	}



	/**
	 * @param TemplateSource $template
	 */
	public function invalidate(TemplateSource $template)
	{
		$this->latteStorage->clean(array(
			Cache::TAGS => array('dbTemplate#' . $template->getId())
		));

		if ($this->cacheStorage !== NULL) {
			$this->cacheStorage->clean(array(
				Cache::TAGS => array('dbTemplate#' . $template->getId())
			));
		}
	}



	/**
	 * @param \Kdyby\Templates\TemplateSource $template
	 */
	public function save(TemplateSource $template)
	{
		static $trigger;
		if (!isset($trigger)) {
			$trigger = $template;
		}

		if ($extended = $template->getExtends()) {
			$this->save($extended);
		}

		$dp = array();
		if ($source = $template->build($this, $dp)) {
			$this->latteStorage->write(self::CACHE_NS . Cache::NAMESPACE_SEPARATOR . $template->getId(), $source, $dp);
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
		$this->invalidate($template);
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
		if (!$template->getId()) {
			$this->save($template);
		}

		$key = self::CACHE_NS . Cache::NAMESPACE_SEPARATOR . $template->getId();
		if ($layoutFile !== NULL) {
			$key .= '.l' . substr(md5(serialize($layoutFile)), 0, 8);
		}

		// load or save
		if (!$cached = $this->latteStorage->read($key)) {
			$dp = array();
			$this->latteStorage->write($key, $template->build($this, $dp, $layoutFile), $dp);
			$cached = $this->latteStorage->read($key);
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
