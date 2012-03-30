<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\ClassLoader\UniversalClassLoader;



// load Nette Framework first
require_once __DIR__ . '/vendor/nette/nette/Nette/loader.php';

// require class loader
require_once __DIR__ . '/vendor/.composer/autoload.php';


// library
$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
	'Kdyby\\Tests' => __DIR__ . '/tests',
	'Kdyby' => __DIR__ . '/libs',
));
$loader->register();

// Doctrine annotations
AnnotationRegistry::registerLoader(function($class) use ($loader) {
   $loader->loadClass($class);
   return class_exists($class, FALSE);
});
AnnotationRegistry::registerFile(__DIR__ . '/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
