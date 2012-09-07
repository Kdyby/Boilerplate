<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Migrations\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SqlDump extends Nette\Object implements \Iterator
{
	/** @var string */
	private $file;

	/** @var resource */
	private $resource;

	/** @var string */
	private $currentSql;

	/** @var array */
	private $sqls = array();

	/** @var integer */
	private $count = 0;



	/**
	 * @param string $file
	 */
	public function __construct($file)
	{
		$this->file = $file;
		@set_time_limit(0); // intentionally @
	}



	/**
	 * @return array
	 */
	public function getSqls()
	{
		if ($this->sqls) {
			return $this->sqls;
		}

		foreach ($this as $sql) {
			$this->sqls[] = $sql;
		}
		return $this->sqls;
	}



	/**
	 * @return string
	 */
	private function fetchOne()
	{
		$this->currentSql = $sql = NULL;
		while (!feof($this->resource())) {
			if (substr($s = fgets($this->resource()), 0, 2) === '--') {
				continue;
			}

			$sql .= $s;
			if (substr(rtrim($s), -1) === ';') {
				$this->currentSql = trim($sql);
				$this->count += 1;
				break;
			}
		}

		if (!$this->currentSql) {
			@fclose($this->resource);
		}

		return $this->currentSql;
	}



	/**
	 * @return resource
	 * @throws \Kdyby\FileNotFoundException
	 */
	private function resource()
	{
		if ($this->resource !== NULL) {
			return $this->resource;
		}

		$this->resource = @fopen($this->file, 'r'); // intentionally @
		if (!$this->resource) {
			throw new Kdyby\FileNotFoundException("Cannot open file '$this->file'.");
		}

		return $this->resource;
	}



	/**
	 * Closes the file.
	 */
	public function __destruct()
	{
		@fclose($this->resource);
	}


	/****************** \Iterator ******************/


	/**
	 * Rewinds to the beginning of the file
	 */
	public function rewind()
	{
		@fseek($this->resource(), 0);
		$this->fetchOne();
	}



	/**
	 * @return string
	 */
	public function current()
	{
		return $this->currentSql;
	}



	/**
	 * @return int
	 */
	public function key()
	{
		return $this->count - 1;
	}



	/**
	 * @return string
	 */
	public function next()
	{
		return $this->fetchOne();
	}



	/**
	 * @return boolean
	 */
	public function valid()
	{
		return (bool)$this->currentSql;
	}

}
