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
Debugger::enable(Nette\Configurator::detectProductionMode());
Debugger::$strictMode = TRUE;


// register Kdyby loader and other (even optional) loaders
require_once KDYBY_DIR . '/Loaders/SplClassLoader.php';
Kdyby\Loaders\SplClassLoader::getInstance(array(
	'Kdyby' => KDYBY_DIR,
	'Doctrine' => LIBS_DIR . '/Doctrine',
	'DoctrineExtensions' => LIBS_DIR . '/Doctrine/DoctrineExtensions',
	'Gedmo' => LIBS_DIR . '/Doctrine/Gedmo',
	'Symfony' => LIBS_DIR . '/Symfony',
	'Zend' => LIBS_DIR . '/Zend', // Supporst only Zend Framework 2
))->register();


// Create Configurator
$configurator = new Kdyby\DI\Configurator;
Nette\Environment::setConfigurator(new Kdyby\DI\Configurator);
