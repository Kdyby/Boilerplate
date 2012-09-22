<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

// take care of autoloading
use Doctrine\Common\Annotations\AnnotationRegistry;

// require class loader
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Kdyby\\Tests', __DIR__);
$loader->add('Kdyby', __DIR__ . '/../libs');

// Doctrine annotations
AnnotationRegistry::registerLoader(callback('class_exists'));

// create container
Kdyby\Tests\Configurator::testsInit(__DIR__)
	->getContainer();

unset($loader); // cleanup
