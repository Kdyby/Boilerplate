<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Diagnostics;

use Doctrine;
use Doctrine\DBAL\Logging\SQLLogger;
use Kdyby;
use Kdyby\Doctrine\SqlException;
use Nette;
use Nette\Diagnostics\Debugger;
use Nette\Database\Connection;



/**
 * @author Filip Procházka
 */
class LastSqlLogger extends Nette\Object implements SQLLogger
{

	/** @var string */
	private $lastQuery;

	/** @var string */
	private $lastParams;

	/** @var string */
	private $lastTypes;



	/**
	 * @param string $sql
	 * @param array $params
	 * @param array $types
	 */
	public function startQuery($sql, array $params = NULL, array $types = NULL)
	{
		$this->lastQuery = $sql;
		$this->lastParams = $params;
		$this->lastTypes = $types;
	}



	/**
	 */
	public function stopQuery()
	{
		$this->lastQuery = NULL;
		$this->lastParams = NULL;
		$this->lastTypes = NULL;
	}



	/**
	 * @param SqlException $e
	 * @return void|array
	 */
	public function renderException($e)
	{
		if (!$e instanceof SqlException) {
			return;
		}

		$h = 'htmlSpecialChars';

		// query
		$s = '<p><b>Query</b></p><table><tr><td class="nette-Doctrine2Panel-sql">';
		$s .= Connection::highlightSql($this->lastQuery);
		$s .= '</td></tr></table>';

		// parameters
		$s .= '<p><b>Parameters</b></p><table>';
		foreach ($this->lastParams as $name => $value) {
			$s .= '<tr><td width="200">' . $h($name) . '</td><td>' . $h($value) . '</td></tr>';
		}
		$s .= '</table>';

		return array(
			'tab' => 'SQL',
			'panel' => $this->renderStyles() . '<div class="nette-inner nette-Doctrine2Panel">' . $s . '</div>',
		);
	}



	/**
	 * @return string
	 */
	protected function renderStyles()
	{
		return '<style> #nette-debug td.nette-Doctrine2Panel-sql { background: white !important }
			#nette-debug .nette-Doctrine2Panel-source { color: #BBB !important }
			#nette-debug nette-Doctrine2Panel tr table { margin: 8px 0; max-height: 150px; overflow:auto } </style>';
	}



	/**
	 * @return LastSqlLogger
	 */
	public static function register()
	{
		$logger = new static;
		Debugger::$blueScreen->addPanel(callback($logger, 'renderException'), 'doctrineSqlException');
		return $logger;
	}

}