<?php

// required constants
define('APP_DIR', __DIR__);
define('VENDORS_DIR', __DIR__ . '/../vendors');

// Take care of autoloading
require_once VENDORS_DIR . '/autoload.php';
require_once APP_DIR . '/../Kdyby/loader.php';

// Setup Nette debuger
Nette\Debug::enable(Nette\Debug::PRODUCTION);
Nette\Debug::$logDirectory = APP_DIR;
Nette\Debug::$maxLen = 4096;

// Init Nette Framework robot loader
$loader = new Nette\Loaders\RobotLoader;
$loader->setCacheStorage(new Nette\Caching\MemoryStorage);
$loader->addDirectory(APP_DIR);
$loader->register();
