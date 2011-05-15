<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Templates;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka
 */
class FileTemplate extends Nette\Templating\FileTemplate
{

	/**
	 * @param array $params
	 */
	public function addParams(array $params)
	{
		$this->setParams($params + $this->getParams());
	}

}