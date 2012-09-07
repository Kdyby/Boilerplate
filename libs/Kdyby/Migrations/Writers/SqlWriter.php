<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Migrations\Writers;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
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
	 * @return bool
	 */
	public function write(array $sqls)
	{
		if (!$sqls) {
			return FALSE;
		}

		foreach ($sqls as $sql) {
			$this->writeSql($sql);
		}

		return (bool)count($sqls);
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
