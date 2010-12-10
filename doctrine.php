<?php

use Nette\Debug;
use Nette\Environment;

define('KDYBY_DIR', __DIR__);

require_once LIBS_DIR . '/Nette/loader.php';
require_once LIBS_DIR . '/Kdyby/Loaders/KdybyLoader.php';

Debug::enable();
Kdyby\Loaders\KdybyLoader::getInstance()->register();

Environment::loadConfig();
Environment::setServiceAlias('Doctrine\\ORM\\EntityManager', 'EntityManager');
Environment::setServiceAlias('Kdyby\\Application\\DatabaseManager', 'DatabaseManager');
