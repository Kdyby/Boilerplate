<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

use Nette\Callback;
use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\Helpers;



/**
 * Bar dump shortcut.
 * @see Nette\Diagnostics\Debugger::barDump
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @param mixed $var
 * @param string $title
 *
 * @return mixed
 */
function bd($var, $title = NULL) {
	return callback('Nette\Diagnostics\Debugger', 'barDump')->invokeArgs(func_get_args());
}



/**
 * Deep dump shortcut.
 * @see Nette\Diagnostics\Debugger::dump
 *
 * @param mixed $var
 * @param integer $maxDepth
 *
 * @return mixed
 */
function dd($var, $maxDepth) {
	$originalDepth = Debugger::$maxDepth;
	Debugger::$maxDepth = $maxDepth;
	Debugger::dump($var);
	Debugger::$maxDepth = $originalDepth;
	return $var;
}



/**
 * Function prints from where were method/function called
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @param int $level
 * @param bool $return
 * @param bool $fullTrace
 */
function wc($level = 1, $return = FALSE, $fullTrace = FALSE) {
	if (Debugger::$productionMode) { return; }

	$o = function ($t) { return (isset($t->class) ? htmlspecialchars($t->class) . "->" : NULL) . htmlspecialchars($t->function) . '()'; };
	$f = function ($t) {
		$file = defined('APP_DIR') ? 'app' . str_replace(realpath(APP_DIR), '', realpath($t->file)) : $t->file;
		return '<a href="' . Helpers::editorLink($t->file, $t->line) . '">' . htmlspecialchars($file) . ':' . (int)$t->line . '</a>';
	};

	$trace = debug_backtrace();
	$target = (object)$trace[$level];
	$caller = (object)$trace[$level+1];
	$message = NULL;

	if ($fullTrace) {
		array_shift($trace);
		foreach ($trace as $call) {
			$message .= $o((object)$call) . " \n";
		}

	} else {
		$message = $o($target) . " called from " . $o($caller) . " (" . $f($caller) . ")";
	}

	if ($return) {
		return strip_tags($message);
	}
	echo "<pre class='nette-dump'>" . nl2br($message) . "</pre>";
}
