<?php

use Nette\Diagnostics\Debugger;

// required constants
define('APP_DIR', __DIR__);
define('TESTS_DIR', __DIR__);
define('TEMP_DIR', __DIR__ . '/temp');
define('VENDORS_DIR', APP_DIR . '/../libs/vendors');
define('LIBS_DIR', VENDORS_DIR);

// Take care of autoloading
require_once VENDORS_DIR . '/autoload.php';

// Setup Nette debuger
Debugger::enable(Debugger::PRODUCTION);
Debugger::$logDirectory = APP_DIR . '/log';
Debugger::$maxLen = 4096;

// Init Nette Framework robot loader
$loader = new Nette\Loaders\RobotLoader;
$loader->setCacheStorage(new Nette\Caching\Storages\MemoryStorage);
$loader->addDirectory(APP_DIR);
$loader->register();

// start session on time
$configurator->container->session->start();