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
 * Outputs the variable content to file
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @param mixed $variable
 * @param int $maxDepth
 *
 * @return mixed
 */
function fd($variable, $maxDepth = 3) {
	$style = <<<CSS
	pre.nette-dump { color: #444; background: white; }
	pre.nette-dump .php-array, pre.nette-dump .php-object { color: #C22; }
	pre.nette-dump .php-string { color: #080; }
	pre.nette-dump .php-int, pre.nette-dump .php-float { color: #37D; }
	pre.nette-dump .php-null, pre.nette-dump .php-bool { color: black; }
	pre.nette-dump .php-visibility { font-size: 85%; color: #999; }
CSS;

	$originalDepth = Debugger::$maxDepth;
	Debugger::$maxDepth = $maxDepth;
	$dump = "<pre class=\"nette-dump\">" . Nette\Diagnostics\Helpers::htmlDump($variable) . "</pre>\n";
	Debugger::$maxDepth = $originalDepth;
	$dump .= "<style>" . $style . "</style>";
	$file = Debugger::$logDirectory . '/dump_' . substr(md5($dump), 0, 6) . '.html';

	file_put_contents($file, $dump);
	Kdyby\Diagnostics\ConsoleDebugger::openFile($file);

	return $variable;
}



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
function dd($var, $maxDepth = 0) {
	if (is_string($var)) {
		$originalLen = Debugger::$maxLen;
		Debugger::$maxLen = $maxDepth;
		Debugger::dump($var);
		Debugger::$maxLen = $originalLen;

	} else {
		$originalDepth = Debugger::$maxDepth;
		Debugger::$maxDepth = $maxDepth;
		Debugger::dump($var);
		Debugger::$maxDepth = $originalDepth;
	}
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
		return Helpers::editorLink($t->file, $t->line);
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
