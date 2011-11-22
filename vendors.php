#!/usr/bin/env php
<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

set_time_limit(0);

if (!is_dir($vendorDir = __DIR__ . '/vendor')) {
	mkdir($vendorDir, 0777, TRUE);
}

$deps = array(
	array('nette', 'http://github.com/nette/nette.git', 'origin/HEAD'),
	array('symfony', 'http://github.com/symfony/symfony.git', 'v2.0.6'),
	array('doctrine', 'http://github.com/doctrine/doctrine2.git', '2.1.2'),
	array('doctrine-dbal', 'http://github.com/doctrine/dbal.git', '2.1.3'),
	array('doctrine-common', 'http://github.com/doctrine/common.git', '2.1.2'),
	array('doctrine-data-fixtures', 'http://github.com/doctrine/data-fixtures.git', 'origin/HEAD'),
	array('doctrine-migrations', 'http://github.com/doctrine/migrations.git', 'origin/HEAD'),
	array('doctrine-extensions', 'http://github.com/beberlei/DoctrineExtensions.git', 'origin/HEAD'),
	array('doctrine-gedmo', 'http://github.com/l3pp4rd/DoctrineExtensions.git', '2.1.x'),
	array('texy', 'http://github.com/dg/texy.git', 'origin/HEAD'),
	array('apigen', 'http://github.com/apigen/apigen.git', '2.3.0'),
);

foreach ($deps as $dep) {
	list($name, $url, $rev) = $dep;

	$installDir = $vendorDir . '/' . $name;
	if (!is_dir($installDir)) {
		echo "> Installing $name\n";
		system(sprintf('git clone %s %s', escapeshellarg($url), escapeshellarg($installDir)));
	}

	echo "> Updating $name\n";
	system(sprintf('cd %s && git fetch origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));
}
