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



/**
 * @see Nette\Diagnostics\Debugger::barDump
 * @author Filip Procházka
 */
function bd($var, $title = NULL) {
	return callback('Nette\Diagnostics\Debugger', 'barDump')->invokeArgs(func_get_args());
}



/**
 * Function prints from where were method/function called
 * @author Filip Procházka
 *
 * @param int $level
 */
function wc($level = 1) {
	if (\Nette\Debug::$productionMode) { return; }

	$o = function ($t) { return (isset($t->class) ? htmlspecialchars($t->class) . "->" : NULL) . htmlspecialchars($t->function) . '()'; };
	$f = function ($t) {
		$file = defined('APP_DIR') ? 'app' . str_replace(realpath(APP_DIR), '', realpath($t->file)) : $t->file;
		return '<a href="' . \Nette\DebugHelpers::editorLink($t->file, $t->line) . '">' . htmlspecialchars($file) . ':' . (int)$t->line . '</a>';
	};

	$trace = debug_backtrace();
	$target = (object)$trace[$level];
	$caller = (object)$trace[$level+1];

	echo "<pre class='nette-dump'>" . $o($target) . " called from " . $o($caller) . " (" . $f($caller) . ")</pre>";
}