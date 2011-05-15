<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

use Nette\Diagnostics\Debugger;

@header('X-Generated-By: Kdyby ;url=www.kdyby.org'); // @ - headers may be sent

define('KDYBY', TRUE);
define('KDYBY_DIR', __DIR__);

if (!defined('NETTE')) {
	if (!defined('LIBS_DIR')) {
		throw new RuntimeException("Nette Framework cannot be loaded! Missing constant LIBS_DIR");
	}

	// Load Nette Framework
	require_once LIBS_DIR . '/Nette/loader.php';
}

// Require shorcut functions
require_once KDYBY_DIR . '/functions.php';


// Configure environment
//Nette\Debug::enable(Nette\Debug::DEVELOPMENT, TEMP_DIR . '/log');
Debugger::enable();
Debugger::$strictMode = TRUE;
//Debug::$maxDepth = 10;
//Debug::$maxLen = 2024;


// register kdyby loader
require_once KDYBY_DIR . '/Loaders/KdybyLoader.php';
Kdyby\Loaders\KdybyLoader::getInstance()->register();


// Create Configurator
Nette\Environment::setConfigurator(new Kdyby\DI\Configurator);