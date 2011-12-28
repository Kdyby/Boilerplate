<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage;

use Assetic;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetWriter extends Assetic\AssetWriter
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
	 * @return string
	 */
	public function getWriteToDir()
	{
		return $this->dir;
	}

}
