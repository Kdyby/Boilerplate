<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Component;

use Nette;
use Nette\String;
use Nette\Web\Html;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Headjs extends Kdyby\Control\LookoutControl
{

	const PACKAGE_FULL = 'head';
	const PACKAGE_MIN = 'head.min';
	const PACKAGE_LOAD = 'head.load.min';

	const EXECUTE_SEQUENCE = 'sequence';
	const EXECUTE_PARALEL = 'paralel';


	/** @var string */
	private $dir;

	/** @var array */
	private $files = array();

	/** @var array */
	private $printed = array();

	/** @var bool */
	private $loaded = FALSE;

	/** @var string */
	private $package;

	/** @var Nette\Caching\Cache */
	private $cache;



	/**
	 * @param Nette\Caching\Cache $cache
	 */
	public function setCache(Nette\Caching\Cache $cache)
	{
		$this->cache = $cache;
	}



	/**
	 * @return Nette\Caching\Cache
	 */
	public function getCache()
	{
		return $this->cache;
	}



	/**
	 * @param string $package
	 */
	public function setPackage($package)
	{
		$this->package = $package;
	}



	/**
	 * @return string
	 */
	public function getPackage()
	{
		return $this->package;
	}



	/**
	 * @param string $dir
	 */
	public function setJavascriptDir($dir)
	{
		$this->dir = $dir;
	}



	/**
	 * @return string
	 */
	public function getJavascriptDir()
	{
		return $this->dir;
	}



	/**
	 * @param string $file
	 */
	public function addScript($file)
	{
		$this->files[] = $file;
	}



	public function viewDefault($file1 = NULL, $file2 = NULL, $file3 = NULL)
	{
		$files = func_get_args();
		$package = $this->package ?: self::PACKAGE_MIN;

		$this->renderHtml($this->dir . '/' . $package . ".js", $files);
	}



	public function viewLoad($file1 = NULL, $file2 = NULL, $file3 = NULL)
	{
		$files = func_get_args();
		$package = $this->package ?: self::PACKAGE_MIN;

		$this->renderHtml($this->dir . '/' . $package . ".js", $files);
	}



	/**
	 * @param string $file
	 * @return boolean
	 */
	protected function isRelative($file)
	{
		return !String::match($file, '~^[a-z0-9]{1,5}:\/\/.+~i');
	}



	/**
	 * @param string $string
	 * @return boolean
	 */
	protected function isClosure($string)
	{
		return String::match($string, '~^\s*?function\s*?\(\s*?\)\s*?\{.*?\}\s*?$~i');
	}



	/**
	 * @param string $src
	 * @param array $files
	 */
	protected function renderHtml($src, $files)
	{
		if (!$this->loaded) {
			echo $this->renderLoad($src), "\n";
			$this->loaded = TRUE;
		}

		$files = array_diff(array_merge($this->files, $files), $this->printed);
		if ($files) {
			if (count($files) === 1 && $this->isClosure(reset($files))) {
				trigger_error(__CLASS__.": won't process function when have no files to load!", E_USER_WARNING);
			}

			if (!$this->cache || !isset($this->cache[$files])) {
				$init = $this->renderInit($files)."\n";
				if ($this->cache && !isset($this->cache[$files])) {
					$this->cache[$files] = $init;
				}

			} else {
				$init = $this->cache[$files];
			}

			$this->printed = array_merge($this->printed, $files);

			echo $init;
		}
	}



	/**
	 * @param string $src
	 * @return string
	 */
	protected function renderLoad($src)
	{
		$script = Html::el('script');
		$script->type = 'text/javascript';
		$script->src = $src;

		return (string)$script;
	}



	/**
	 * @param array $files
	 * @param string $execute
	 * @return string
	 */
	protected function renderInit(array $files, $execute = self::EXECUTE_SEQUENCE)
	{
		$script = Html::el('script');
		$script->type = 'text/javascript';
		$script->src = NULL;

		if ($execute === self::EXECUTE_SEQUENCE) {
			$chain = array();
			foreach ($files as $file) {
				if ($this->isClosure($file)) {
					$chain[] = trim($file);

				} else {
					$chain[] = '"'. ($this->isRelative($file) ? $this->dir.'/'.$file : $file) .'"';
				}
			}

			$script->setHtml("\n\thead.js(".implode(', ', $chain).");\n");

		} else {
			$chain = NULL;
			foreach ($files as $file) {
				$file = $this->isRelative($file) ? $this->dir . '/' . $file : $file;
				$chain .= '.js("'.$file.'")';
			}
			$script->setHtml("\n\thead".$chain.";\n");
		}

		return (string)$script;
	}

}