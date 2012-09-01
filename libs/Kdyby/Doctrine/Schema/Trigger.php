<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Schema;

use Doctrine\DBAL\Schema;use string;
use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @method \Kdyby\Doctrine\Schema\Trigger insert(string $table, array $values)
 * @method \Kdyby\Doctrine\Schema\Trigger insertSelect(string $targetTable, \Doctrine\DBAL\Schema\Table $sourceTable)
 * @method \Kdyby\Doctrine\Schema\Trigger update(string $table, array $pairs, string $where = NULL)
 * @method \Kdyby\Doctrine\Schema\Trigger declare(string $variable, string $type)
 * @method \Kdyby\Doctrine\Schema\Trigger set(string $variable, string $value)
 * @method \Kdyby\Doctrine\Schema\Trigger if(string $condition, callback $statement)
 *
 * @method static \Kdyby\Doctrine\Schema\Trigger beforeInsert(string $table, string $name)
 * @method static \Kdyby\Doctrine\Schema\Trigger afterInsert(string $table, string $name)
 * @method static \Kdyby\Doctrine\Schema\Trigger beforeUpdate(string $table, string $name)
 * @method static \Kdyby\Doctrine\Schema\Trigger afterUpdate(string $table, string $name)
 * @method static \Kdyby\Doctrine\Schema\Trigger beforeDelete(string $table, string $name)
 * @method static \Kdyby\Doctrine\Schema\Trigger afterDelete(string $table, string $name)
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
	 * @var array
	 */
	public $trigger = array();

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
	 * @return \Kdyby\Doctrine\Schema\Trigger
	 */
	public function add($sql)
	{
		$this->trigger[] = $sql;
		return $this;
	}



	/**
	 * @param string $name
	 * @param array $args
	 */
	public function __call($name, $args)
	{
		$c = function ($l) {
			return '`' . implode('`, `', (array)$l) . '`';
		};

		switch ($name) {
			case 'insert':
				list($table, $values) = $args;
				$this->add("INSERT INTO `{$table}` " . $this->formatValues($values));
				break;

			case 'insertSelect':
				/** @var \Doctrine\DBAL\Schema\Table $sourceTable */
				list($targetTable, $sourceTable, $options) = $args + array(2 => array());
				$where = !empty($options['where']) ? $options['where']: array();
				$addValues = !empty($options['values']) ? $options['values'] : array();

				$columns = array_map(function (Schema\Column $column) { return $column->getName(); }, $sourceTable->getColumns());

				foreach ($addValues as $key => $value) {
					unset($addValues[$key]);
					$value = static::formatValue($key, $value);
					if ($i = array_search($key, $columns, TRUE)) {
						unset($columns[$i]);
					}
					$addValues[$key] = $value;
				}

				$insColumns = array_merge($columns, array_keys((array)$addValues));

				$this->add("INSERT INTO `{$targetTable}` ({$c($insColumns)}) SELECT {$c($columns)} " .
					($addValues ? ', ' . implode(', ', $addValues) : '') .
					" FROM {$sourceTable->getName()}" .
					($where ? " WHERE " . $where : ''));
				break;

			case 'update':
				list($table, $pairs, $options) = $args + array(2 => array());
				$where = &$options['where'];
				$orderBy = &$options['orderBy'];
				$limit = &$options['limit'];

				$this->add("UPDATE `{$table}` SET " . $this->formatPairs($pairs) .
					($where ? " WHERE " . $where : NULL) .
					($orderBy ? " ORDER BY " . $orderBy : NULL) .
					($limit ? " LIMIT " . $limit : NULL));
				break;

			case 'declare':
				list($variable, $type) = $args;
				$this->add("DECLARE `{$variable}` {$type}");
				break;

			case 'set':
				list($variable, $value) = $args;
				$this->add("SET @{$variable} := {$value}");
				break;

			case 'if':
				list($condition, $callback) = $args;

				// temporary clean the trigger
				$tmp = $this->trigger;
				$this->trigger = array();

				// create statement
				callback($callback)->invoke($this);
				$statement = $this->trigger;

				// restore trigger
				$this->trigger = $tmp;

				// compound statement
				$this->add("IF $condition THEN \n" . array_shift($statement));
				$this->trigger = array_merge($this->trigger, $statement);
				$this->add('END IF');
				break;

			default:
				$this->add(strtoupper($name) . ' ' . implode(' ', $args));

				break;
		}

		return $this;
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		$for = $this->forEachRow ? 'FOR EACH ROW' : NULL;
		$trigger = implode(";\n", $this->trigger) . ';';

		return <<<TRG
CREATE TRIGGER {$this->name} {$this->when} {$this->action} ON `{$this->table}`
	$for BEGIN
		{$trigger}
	END
TRG;
	}



	/**
	 * @return string
	 */
	public function getDropSql()
	{
		return "DROP TRIGGER IF EXISTS {$this->name};";
	}



	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public static function __callStatic($name, $args)
	{
		if ($words = Strings::matchAll($name, '~((?:^|[A-Z])[a-z]+)~')) {
			$when = reset($words[0]);
			$action = reset($words[1]);

			if (!defined('static::DO_' . strtoupper($when)) || !defined('static::ACTION_' . strtoupper($action))) {
				return parent::__callStatic($name, $args);
			}

			list($table, $name) = $args;
			return new static($table, $name, $when, $action);
		}

		return parent::__callStatic($name, $args);
	}



	/**
	 * @param array|string $values
	 * @return string
	 */
	private static function formatValues($values)
	{
		if (!is_array($values)) {
			return $values;
		}

		$valuesSql = $keysSql = array();
		foreach ($values as $key => $value) {
			$valuesSql[] = static::formatValue($key, $value);
			$keysSql[] = $key;
		}

		return "(`" . implode("`, `", $keysSql) . "`) VALUES (" . implode(", ", $valuesSql) . ")";
	}



	/**
	 * @param array $pairs
	 * @return string
	 */
	private static function formatPairs(array $pairs)
	{
		$sql = array();
		foreach ($pairs as $key => $value) {
			$value = static::formatValue($key, $value);
			$sql[] = "`$key` = {$value}";
		}

		return implode(', ', $sql);
	}



	/**
	 * @param string $key
	 * @param string $value
	 * @return string
	 */
	private static function formatValue(&$key, $value)
	{
		$type = NULL;
		if (strpos($key, '%') !== FALSE) {
			list($key, $type) = explode('%', $key);
		}

		return $type !== 'sql'
			? "'" . addslashes($value) . "'"
			: $value;
	}

}
