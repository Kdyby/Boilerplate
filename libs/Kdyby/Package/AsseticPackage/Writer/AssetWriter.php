<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage\Writer;

use Assetic;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetWriter extends Assetic\AssetWriter implements Kdyby\Package\AsseticPackage\IWriter
{

	/** @var string */
	private $dir;

	/** @var string */
	private $baseUrl;



	/**
	 * @param string $dir
	 * @param \Nette\Http\Request $httpRequest
	 * @param string $prefix
	 */
	public function __construct($dir, Nette\Http\Request $httpRequest, $prefix)
	{
		parent::__construct($dir);
		$this->dir = $dir;
		$this->baseUrl = rtrim($httpRequest->getUrl()->getBaseUrl(), '/') . '/' . $prefix;
	}



	/**
	 * @param $assetOutput
	 *
	 * @return string
	 */
	public function getAssetUrl($assetOutput)
	{
		return $this->baseUrl . '/' . $assetOutput;
	}



	/**
	 * @param $assetOutput
	 *
	 * @return string
	 */
	public function getAssetRealpath($assetOutput)
	{
		return $this->dir . '/' . $assetOutput;
	}



	/**
	 * @param string $assetOutput
	 * @param string $unixtime
	 *
	 * @return string
	 */
	public function isFresh($assetOutput, $unixtime)
	{
		$file = $this->dir . '/' . $assetOutput;
		return file_exists($file) && filemtime($file) > $unixtime;
	}

}
