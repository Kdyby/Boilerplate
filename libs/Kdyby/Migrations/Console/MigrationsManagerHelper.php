<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations\Console;

use Kdyby;
use Nette;
use Symfony;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MigrationsManagerHelper extends Symfony\Component\Console\Helper\Helper
{

	/** @var \Kdyby\Migrations\MigrationsManager */
	protected $manager;



	/**
	 * @param \Kdyby\Migrations\MigrationsManager $manager
	 */
	public function __construct(Kdyby\Migrations\MigrationsManager $manager)
	{
		$this->manager = $manager;
	}



	/**
	 * @return \Kdyby\Migrations\MigrationsManager
	 */
	public function getMigrationsManager()
	{
		return $this->manager;
	}



	/**
	 * @see \Symfony\Component\Console\Helper\Helper::getName()
	 */
	public function getName()
	{
		return 'migrationsManager';
	}

}
