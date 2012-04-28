<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations;

use Kdyby;
use Kdyby\Tools\Filesystem;
use Nette;
use Nette\Utils\Finder;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class QueryWriter extends Nette\Object
{

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var \Kdyby\Packages\Package
	 */
	protected $package;

	/**
	 * @var string
	 */
	protected $dir;

	/**
	 * @var string
	 */
	protected $file;



	/**
	 * @param string $version
	 * @param \Kdyby\Packages\Package $package
	 */
	public function __construct($version, Kdyby\Packages\Package $package)
	{
		$this->version = $version;
		$this->package = $package;

		$this->dir = $this->package->getPath() . '/Migration';

		if (!is_dir($this->dir)) {
			Filesystem::mkDir($this->dir);
		}
	}



	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}



	/**
	 * Finds any existing migrations with same version and deletes them.
	 */
	public function removeExisting()
	{
		foreach (Finder::findFiles($this->version . '*')->in($this->dir) as $file) {
			Filesystem::rm($file);
		}
	}



	/**
	 * @param array $sqls
	 * @return boolean
	 */
	abstract public function write(array $sqls);

}
