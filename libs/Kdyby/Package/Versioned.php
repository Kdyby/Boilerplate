<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class Versioned extends Nette\Object
{

	/** @var string */
    protected $version = '0';



	/**
	 * @param Versioned $resource
	 * @return boolean
	 */
	public function isAcceptable(Versioned $resource)
	{
		return version_compare($this->version, $resource->version, '>=');
	}

}