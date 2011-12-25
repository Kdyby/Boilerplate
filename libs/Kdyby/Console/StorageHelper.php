<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Console;

use Kdyby;
use Nette;
use Symfony;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class StorageHelper extends Symfony\Component\Console\Helper\Helper
{

	/** @var Nette\Caching\IStorage */
	protected $storage;



	/**
	 * @param \Nette\Caching\IStorage $storage
	 */
	public function __construct(Nette\Caching\IStorage $storage)
	{
		$this->storage = $storage;
	}



	/**
	 * @return \Nette\Caching\IStorage
	 */
	public function getStorage()
	{
		return $this->storage;
	}



	/**
	 * @see \Symfony\Component\Console\Helper\Helper::getName()
	 */
	public function getName()
	{
		return 'storage';
	}

}
