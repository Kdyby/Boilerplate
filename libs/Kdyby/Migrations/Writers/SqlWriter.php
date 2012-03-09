<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations\Writers;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SqlWriter extends Kdyby\Migrations\QueryWriter
{

	/**
	 * @param string $version
	 * @param \Kdyby\Packages\Package $package
	 */
	public function __construct($version, Kdyby\Packages\Package $package)
	{
		parent::__construct($version, $package);
		$this->file = $this->dir . '/' . $this->version . '.sql';
	}



	/**
	 * @param array $sqls
	 */
	public function write(array $sqls)
	{
		if (!$sqls) {
			return;
		}

		foreach ($sqls as $sql) {
			$this->writeSql($sql);
		}
	}



	/**
	 * @param string $sql
	 */
	private function writeSql($sql)
	{
		if (!file_exists($this->file)) {
			touch($this->file);
		}

		file_put_contents($this->file, $sql . ";\n", FILE_APPEND);
	}

}
