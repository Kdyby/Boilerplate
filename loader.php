<?php

namespace Kdyby;

use Kdyby;
use Nette\Debug;
use Nette\Environment;


@header('X-Generated-By: Kdyby F-CMS ;url=www.kdyby.org'); // @ - headers may be sent

define('KDYBY_DIR', __DIR__);

// Step 1: Load libraries
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
// 1a) Load Nette Framework
require LIBS_DIR . '/Nette/loader.php';

require LIBS_DIR . '/dibi/dibi.php';


// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
<<<<<<< HEAD
Debug::enable('127.0.0.1');
=======
Debug::enable(Debug::DEVELOPMENT, TEMP_DIR . '/log'); //'127.0.0.1', '77.240.176.168'
>>>>>>> 1957ee3... Added log directory
Debug::$strictMode = TRUE;
//Debug::$maxDepth = 10;
//Debug::$maxLen = 2024;

$session = Environment::getSession();
$session->setCookieParams('/', '.unired.loc');
$session->setExpiration("+ 365 days");
$session->start();


require_once KDYBY_DIR . '/Loaders/KdybyLoader.php';

Kdyby\Loaders\KdybyLoader::getInstance()->register();


// 2b) load configuration from config.ini file
Environment::loadConfig();
