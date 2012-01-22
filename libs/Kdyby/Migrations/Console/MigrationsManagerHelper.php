<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations\Console;

use Kdyby;
use Nette;
use Symfony;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
	public function getManager()
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
