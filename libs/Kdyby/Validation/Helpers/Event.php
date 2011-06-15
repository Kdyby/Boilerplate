<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation\Helpers;

use Kdyby;
use Kdyby\Validation;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property-read string $name
 */
class Event extends Nette\Object implements Validation\IHelper
{

	const onFlushInsert = 'flush:insert';
	const onFlushUpdate = 'flush:update';

	/** @var name */
	public $name;



	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

}