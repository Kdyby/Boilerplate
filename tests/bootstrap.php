<?php

// take care of autoloading
require_once __DIR__ . '/../autoload.php';

// create container
Kdyby\Tests\Configurator::testsInit(__DIR__)
	->getContainer();
