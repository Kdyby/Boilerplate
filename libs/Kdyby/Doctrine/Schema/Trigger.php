<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Schema;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Trigger extends Nette\Object
{
	const DO_BEFORE = 'BEFORE';
	const DO_AFTER = 'AFTER';

	const ACTION_INSERT = 'INSERT';
	const ACTION_UPDATE = 'UPDATE';
	const ACTION_DELETE = 'DELETE';

	/**
	 * @var string
	 */
	public $when = self::DO_BEFORE;

	/**
	 * @var string
	 */
	public $action = self::ACTION_UPDATE;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $table;

	/**
	 * @var string
	 */
	public $trigger;

	/**
	 * @var bool
	 */
	public $forEachRow = TRUE;



	/**
	 * @param string $table
	 * @param string $name
	 * @param string $when
	 * @param string $action
	 */
	public function __construct($table, $name, $when = self::DO_BEFORE, $action = self::ACTION_UPDATE)
	{
		$this->table = $table;
		$this->action = strtoupper($action);
		$this->when = strtoupper($when);
		$this->setName($name);
	}



	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $this->table . '_' . $name . '_' .
			strtolower($this->when[0]) . // first letter
			strtolower($this->action[0]); // first letter
	}



	/**
	 * @param string $sql
	 */
	public function add($sql)
	{
		if ($this->trigger) {
			$this->trigger .= "\n";
		}
		$this->trigger .= $sql;
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		$for = $this->forEachRow ? 'FOR EACH ROW' : NULL;

		return <<<TRG
DROP TRIGGER IF EXISTS {$this->name};
DELIMITER //
CREATE TRIGGER {$this->name} {$this->when} {$this->action} ON `{$this->table}`
	$for BEGIN
		{$this->trigger}
	END //
DELIMITER ;
TRG;
	}

}
