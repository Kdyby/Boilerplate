<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

use Doctrine\Common\Annotations\AnnotationRegistry;

// require class loader
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require_once __DIR__ . '/vendor/autoload.php';
$loader->add('Kdyby\\Tests', __DIR__ . '/tests');
$loader->add('Kdyby', __DIR__ . '/libs');

// Doctrine annotations
AnnotationRegistry::registerLoader(callback('class_exists'));

unset($loader); // cleanup
