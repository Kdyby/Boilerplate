<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Config;

use Doctrine;
use Kdyby;
use Kdyby\Config\Setting;
use Nette;



/**
 * @author Filip Procházka
 */
class SettingsData extends Doctrine\Common\DataFixtures\AbstractFixture
{

	/**
	 * @param Doctrine\ORM\EntityManager $manager
	 */
	public function load($manager)
	{
		$settings = array();
		$settings[] = new Setting('password', 'database', 'root');
		$settings[] = new Setting('username', 'database', 'Le root');
		$settings[] = new Setting('host', 'database', 'localhost');
		$settings[] = new Setting('port', 'database', '3306');
		$settings[] = new Setting('imageDir', NULL, '%appDir%/data');

		$manager->getRepository('Kdyby\Config\Setting')->save($settings);
	}

}