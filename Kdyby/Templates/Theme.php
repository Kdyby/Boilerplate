<?php

namespace Kdyby\Templates;

use Kdyby;
use Nette;



/**
 * @property-read string $link
 */
class Theme extends Nette\Object
{

	/** @var string */
	private $basePath;

	/** @var string */
	private $dir;



	/**
	 * @param string $baseUri
	 * @param string $dir
	 */
	public function __construct($baseUri, $dir)
	{
		$this->basePath = preg_replace('#https?://[^/]+#A', '', rtrim($baseUri, '/'));
		$this->dir = $dir;
	}



	/**
	 * @param string $dir
	 */
	public function switchTheme($dir)
	{
		$this->dir = $dir;
	}



	/**
	 * @return string
	 */
	public function getLink()
	{
		return $this->basePath . '/' . basename($this->dir);
	}

}