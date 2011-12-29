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



	/**
	 * @param string $dir
	 */
	public function __construct($dir)
	{
		parent::__construct($dir);
		$this->dir = $dir;
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
