<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * This source file is subject to the "Kdyby license", and/or
 * GPL license. For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


use Nette\Debug;
use Nette\Environment;


@header('X-Generated-By: Kdyby F-CMS ;url=www.kdyby.org'); // @ - headers may be sent

define('KDYBY_DIR', __DIR__);

// Step 1: Load libraries
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
// 1a) Load Nette Framework
require LIBS_DIR . '/Nette/loader.php';


// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable(Debug::DEVELOPMENT, TEMP_DIR . '/log');
Debug::$strictMode = TRUE;
//Debug::$maxDepth = 10;
//Debug::$maxLen = 2024;


// register kdyby loader
require_once KDYBY_DIR . '/Loaders/KdybyLoader.php';
Kdyby\Loaders\KdybyLoader::getInstance()->register();


// configure environment
Kdyby\Configurator::setupSession(Environment::getSession());
Environment::setServiceAlias('Doctrine\\ORM\\EntityManager', 'EntityManager');
Environment::setServiceAlias('Kdyby\\Application\\DatabaseManager', 'DatabaseManager');
