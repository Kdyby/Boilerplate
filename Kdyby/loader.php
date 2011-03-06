<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * This source file is subject to the "Kdyby license", and/or
 * GPL license. For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */

@header('X-Generated-By: Kdyby CMF ;url=www.kdyby.org'); // @ - headers may be sent

define('KDYBY', TRUE);
define('KDYBY_DIR', __DIR__);

if (!defined('NETTE')) {
	if (!defined('LIBS_DIR')) {
		throw new RuntimeException("Nette Framework cannot be loaded! Missing constant LIBS_DIR");
	}

	// Load Nette Framework
	require_once LIBS_DIR . '/Nette/loader.php';
}

// helper
function cd() {
	foreach (func_get_args() as $arg) {
		\Nette\Debug::barDump($arg);
	}
}

// Configure environment
// enable Nette\Debug for better exception and error visualisation
//Nette\Debug::enable(Nette\Debug::DEVELOPMENT, TEMP_DIR . '/log');
Nette\Debug::enable();
Nette\Debug::$strictMode = TRUE;
//Debug::$maxDepth = 10;
//Debug::$maxLen = 2024;


// register kdyby loader
require_once KDYBY_DIR . '/Loaders/KdybyLoader.php';
Kdyby\Loaders\KdybyLoader::getInstance()->register();


// configure environment
Nette\Environment::setServiceAlias('Doctrine\\ORM\\EntityManager', 'EntityManager');
