<?php

# Nette Framework
if (is_dir(VENDORS_DIR . '/nette')) {
	require_once VENDORS_DIR . '/nette/Nette/loader.php';
}

# Texy! 
if (is_dir(VENDORS_DIR . '/texy')) {
	require_once VENDORS_DIR . '/texy/Texy/Texy.php';
}

# Doctrine
if (is_dir(VENDORS_DIR . '/doctrine') && is_dir(VENDORS_DIR . '/doctrine-common') && is_dir(VENDORS_DIR . '/doctrine-dbal')) {
	require_once VENDORS_DIR . '/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

	$loader = new Doctrine\Common\ClassLoader('Doctrine\Common', VENDORS_DIR . '/doctrine-common/lib/Doctrine/Common');
	$loader->register();

	$loader = new Doctrine\Common\ClassLoader('Doctrine\DBAL', VENDORS_DIR . '/doctrine-dbal/lib/Doctrine/DBAL');
	$loader->register();

	$loader = new Doctrine\Common\ClassLoader('Doctrine\ORM', VENDORS_DIR . '/doctrine/lib/Doctrine/ORM');
	$loader->register();

	if (is_dir(VENDORS_DIR . '/doctrine-beberlei-extensions')) {
		$loader = new Doctrine\Common\ClassLoader('DoctrineExtensions', VENDORS_DIR . '/doctrine-berberlei-extensions/lib/DoctrineExtensions');
		$loader->register();
	}

	if (is_dir(VENDORS_DIR . '/doctrine-gedmo-extensions')) {
		$loader = new Doctrine\Common\ClassLoader('Gedmo', VENDORS_DIR . '/doctrine-gedmo-extensions/lib/Gedmo');
		$loader->register();
	}
}

// TODO: symfony