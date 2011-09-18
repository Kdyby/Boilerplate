<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

// Nette
require_once VENDORS_DIR . '/nette/Nette/loader.php';

// Kdyby
require_once __DIR__ . '/../Kdyby/loader.php';

// Doctrine, Symfony
Kdyby\Loaders\SplClassLoader::getInstance()->addNamespaces(array(
	'Doctrine\Common' => VENDORS_DIR . '/doctrine-common/lib/Doctrine/Common',
	'Doctrine\Common\DataFixtures' => VENDORS_DIR . '/doctrine-data-fixtures/lib/Doctrine/Common/DataFixtures',
	'Doctrine\DBAL' => VENDORS_DIR . '/doctrine-dbal/lib/Doctrine/DBAL',
	'Doctrine\DBAL\Migrations' => VENDORS_DIR . '/doctrine-migrations/lib/Doctrine/DBAL',
	'Doctrine\ORM' => VENDORS_DIR . '/doctrine/lib/Doctrine/ORM',
	'Doctrine\CouchDB' => VENDORS_DIR . '/doctrine-couchdb/lib/Doctrine/CouchDB',
	'Doctrine\ODM\CouchDB' => VENDORS_DIR . '/doctrine-couchdb/lib/Doctrine/ODM/CouchDB',
	'DoctrineExtensions' => VENDORS_DIR . '/doctrine-beberlei-extensions/lib/DoctrineExtensions',
	'Gedmo' => VENDORS_DIR . '/doctrine-gedmo-extensions/lib/Gedmo',
	'Symfony' => VENDORS_DIR . '/symfony/src/Symfony',
));

// Texy
require_once VENDORS_DIR . '/texy/Texy/Texy.php';