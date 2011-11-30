<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FileLoaderImportLogger extends Nette\Object
{

	/** @var array */
	private $calls = array();



	/**
	 * @param mixed $resource       A Resource
	 * @param string $type           The resource type
	 * @param Boolean $ignoreErrors   Whether to ignore import errors or not
	 * @param string $sourceResource The original resource importing the new resource
	 */
	public function log($resource, $type, $ignoreErrors, $sourceResource)
	{
		$this->calls[] = get_defined_vars();
	}



	/**
	 * @return array
	 */
	public function getCalls()
	{
		return $this->calls;
	}

}