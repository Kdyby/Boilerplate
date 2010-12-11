<?php

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


// setup session
$session = Environment::getSession();
if (!$session->isStarted()) {
	$domain = Kdyby\Web\HttpHelpers::getDomain()->domain;
	$session->setCookieParams('/', '.'.$domain);
	$session->setExpiration(Nette\Tools::YEAR);

	if (!$session->exists()) {
		$session->start();
	}
}


// 2b) load configuration from config.ini file
Environment::loadConfig();

Environment::setServiceAlias('Doctrine\\ORM\\EntityManager', 'EntityManager');
Environment::setServiceAlias('Kdyby\\Application\\DatabaseManager', 'DatabaseManager');
